<?php

namespace App\Http\Controllers\Insumos;

use App\Exports\Insumos\InsumoNotesExport;
use App\Http\Controllers\Controller;
use App\Models\Details_Insumos;
use App\Models\Insumo;
use App\Models\Insumos_Notes;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class InsumoNoteController extends Controller
{
    private array $allowedPerPage = ['10', '20', '30', '50', '100', 'all'];

    private function resolvePerPage(Request $request): string
    {
        $perPage = (string) $request->input('per_page', '10');

        if (! in_array($perPage, $this->allowedPerPage, true)) {
            return '10';
        }

        return $perPage;
    }

    private function notesQuery(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $adminId = $request->input('admin_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $period = $request->input('period', 'all');
        $sort = $request->input('sort', 'recent');

        return Insumos_Notes::query()
            ->with([
                'users:id,name,email',
                'details_insumos.insumos:id,name,amount,price',
            ])
            ->withSum('details_insumos as used_amount_sum', 'amount')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('date', 'like', "%{$search}%")
                        ->orWhere('hour', 'like', "%{$search}%")
                        ->orWhereHas('users', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('details_insumos.insumos', function ($insumoQuery) use ($search) {
                            $insumoQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($adminId, function ($query) use ($adminId) {
                $query->where('users_admin_id', $adminId);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('date', '<=', $dateTo);
            })
            ->when(! $dateFrom && ! $dateTo && $period !== 'all', function ($query) use ($period) {
                $now = Carbon::now('America/La_Paz');

                $startDate = match ($period) {
                    'today' => $now->copy()->toDateString(),
                    'this_week' => $now->copy()->startOfWeek()->toDateString(),
                    'this_month' => $now->copy()->startOfMonth()->toDateString(),
                    'last_2_months' => $now->copy()->subMonths(2)->startOfDay()->toDateString(),
                    'last_6_months' => $now->copy()->subMonths(6)->startOfDay()->toDateString(),
                    'last_year' => $now->copy()->subYear()->startOfDay()->toDateString(),
                    'last_2_years' => $now->copy()->subYears(2)->startOfDay()->toDateString(),
                    default => null,
                };

                if ($startDate) {
                    $query->whereDate('date', '>=', $startDate);
                }
            })
            ->when($sort === 'highest_amount', function ($query) {
                $query
                    ->orderByDesc('used_amount_sum')
                    ->orderByDesc('date')
                    ->orderByDesc('hour')
                    ->orderByDesc('id');
            })
            ->when($sort === 'lowest_amount', function ($query) {
                $query
                    ->orderBy('used_amount_sum')
                    ->orderByDesc('date')
                    ->orderByDesc('hour')
                    ->orderByDesc('id');
            })
            ->when($sort === 'oldest', function ($query) {
                $query
                    ->orderBy('date')
                    ->orderBy('hour')
                    ->orderBy('id');
            })
            ->when(! in_array($sort, ['highest_amount', 'lowest_amount', 'oldest'], true), function ($query) {
                $query
                    ->orderByDesc('date')
                    ->orderByDesc('hour')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
            });
    }

    private function buildStats($query): array
    {
        $noteIds = (clone $query)->pluck('id');

        $totalUsed = (int) Details_Insumos::query()
            ->whereIn('insumos_notes_id', $noteIds)
            ->sum('amount');

        $uniqueInsumos = (int) Details_Insumos::query()
            ->whereIn('insumos_notes_id', $noteIds)
            ->distinct('insumos_id')
            ->count('insumos_id');

        $maxUsage = (int) Details_Insumos::query()
            ->selectRaw('SUM(amount) as total_used')
            ->whereIn('insumos_notes_id', $noteIds)
            ->groupBy('insumos_notes_id')
            ->orderByDesc('total_used')
            ->value('total_used');

        return [
            'notes' => (int) (clone $query)->count(),
            'total_used' => $totalUsed,
            'unique_insumos' => $uniqueInsumos,
            'max_usage' => $maxUsage,
        ];
    }

    public function index(Request $request)
    {
        $perPage = $this->resolvePerPage($request);
        $query = $this->notesQuery($request);

        $stats = $this->buildStats(clone $query);
        $totalRows = (clone $query)->count();

        $notes = $query
            ->paginate($perPage === 'all' ? max($totalRows, 1) : (int) $perPage)
            ->withQueryString();

        return Inertia::render('Insumos/Notes/Index', [
            'notes' => $notes,

            'insumos' => Insumo::query()
                ->select('id', 'name', 'amount', 'price')
                ->orderBy('name')
                ->get(),

            'admins' => User::role(['Master', 'Administrador'])
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get(),

            'filters' => [
                'search' => trim((string) $request->input('search', '')),
                'admin_id' => $request->input('admin_id', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
                'period' => $request->input('period', 'all'),
                'sort' => $request->input('sort', 'recent'),
                'per_page' => $perPage,
            ],

            'stats' => $stats,
            'allowedPerPage' => $this->allowedPerPage,
        ]);
    }

    public function store(Request $request)
    {
        $adminId = Auth::id();

        if (! $adminId) {
            abort(403, 'No tienes una sesión válida.');
        }

        $validated = $request->validate([
            'details' => ['required', 'array', 'min:1'],
            'details.*.insumos_id' => ['required', 'exists:insumos,id'],
            'details.*.amount' => ['required', 'integer', 'min:1'],
        ], [
            'details.required' => 'Debe agregar al menos un insumo utilizado.',
            'details.array' => 'El detalle de insumos no tiene el formato correcto.',
            'details.min' => 'Debe agregar al menos un insumo utilizado.',

            'details.*.insumos_id.required' => 'Debe seleccionar un insumo.',
            'details.*.insumos_id.exists' => 'Uno de los insumos seleccionados no existe.',

            'details.*.amount.required' => 'Debe ingresar la cantidad utilizada.',
            'details.*.amount.integer' => 'La cantidad debe ser un número entero.',
            'details.*.amount.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        DB::transaction(function () use ($validated, $adminId) {
            $now = now('America/La_Paz');

            $note = Insumos_Notes::create([
                'hour' => $now->format('H:i:s'),
                'date' => $now->toDateString(),
                'users_admin_id' => $adminId,
            ]);

            $details = collect($validated['details'])
                ->groupBy('insumos_id')
                ->map(function ($items, $insumoId) {
                    return [
                        'insumos_id' => (int) $insumoId,
                        'amount' => (int) $items->sum('amount'),
                    ];
                })
                ->values();

            foreach ($details as $detail) {
                $insumo = Insumo::query()
                    ->where('id', $detail['insumos_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $amount = (int) $detail['amount'];

                if ($insumo->amount < $amount) {
                    throw ValidationException::withMessages([
                        'details' => "Stock insuficiente para {$insumo->name}. Stock actual: {$insumo->amount}. Cantidad solicitada: {$amount}.",
                    ]);
                }

                Details_Insumos::create([
                    'insumos_id' => $insumo->id,
                    'insumos_notes_id' => $note->id,
                    'amount' => $amount,
                ]);

                $insumo->decrement('amount', $amount);
            }
        });

        return back()->with('success', 'Nota de insumos registrada y stock descontado correctamente.');
    }

    public function destroy(Insumos_Notes $insumosNote)
    {
        DB::transaction(function () use ($insumosNote) {
            $insumosNote->load('details_insumos');

            foreach ($insumosNote->details_insumos as $detail) {
                $insumo = Insumo::query()
                    ->where('id', $detail->insumos_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $insumo->increment('amount', $detail->amount);
            }

            $insumosNote->details_insumos()->delete();
            $insumosNote->delete();
        });

        return back()->with('success', 'Nota eliminada y stock restaurado correctamente.');
    }

    public function exportExcel(Request $request)
    {
        $notes = $this->notesQuery($request)->get();

        return Excel::download(
            new InsumoNotesExport($notes),
            'notas_insumos.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $notes = $this->notesQuery($request)->get();

        $pdf = Pdf::loadView('exports.insumos.insumo-notes-pdf', [
            'title' => 'Reporte de notas de insumos utilizados',
            'notes' => $notes,
        ])->setPaper('letter', 'landscape');

        return $pdf->download('notas_insumos.pdf');
    }

    public function exportTxt(Request $request)
    {
        $notes = $this->notesQuery($request)->get();

        $content = "NOTAS DE INSUMOS UTILIZADOS\n";
        $content .= "Churrasquería Roberto\n";
        $content .= "Generado: " . now('America/La_Paz')->format('d/m/Y H:i:s') . "\n\n";

        foreach ($notes as $note) {
            $content .= "Fecha: {$note->date}\n";
            $content .= "Hora: {$note->hour}\n";
            $content .= "Administrador: " . ($note->users?->name ?? 'Sin usuario') . "\n";
            $content .= "Detalle:\n";

            foreach ($note->details_insumos as $detail) {
                $insumoName = $detail->insumos?->name ?? 'Insumo eliminado';

                $content .= "- {$insumoName} | Cantidad utilizada: {$detail->amount}\n";
            }

            $content .= "----------------------------------------\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="notas_insumos.txt"',
        ]);
    }
}
