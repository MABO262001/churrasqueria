<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', '10');

        $query = Table::query()
            ->withCount(['details_reservations', 'sales_notes']);

        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        if ($request->filled('state')) {
            if ($request->state === 'Sin estado') {
                $query->whereNull('state');
            } else {
                $query->where('state', $request->state);
            }
        }

        $query->orderBy('id', 'desc');

        $tables = $this->paginate($query, $perPage)
            ->withQueryString()
            ->through(function ($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'amount' => $table->amount,
                    'state' => $table->state,
                    'computed_state' => $table->state ?? Table::STATE_AVAILABLE,
                    'details_reservations_count' => $table->details_reservations_count,
                    'sales_notes_count' => $table->sales_notes_count,
                    'created_at' => optional($table->created_at)->format('d/m/Y H:i'),
                ];
            });

        return Inertia::render('Reservations/Tables/Index', [
            'tables' => $tables,
            'states' => Table::states(),
            'filters' => [
                'search' => $request->input('search', ''),
                'state' => $request->input('state', ''),
                'per_page' => $perPage,
            ],
            'stats' => [
                'total' => Table::count(),
                'available' => Table::whereNull('state')->orWhere('state', Table::STATE_AVAILABLE)->count(),
                'inactive' => Table::where('state', Table::STATE_INACTIVE)->count(),
                'maintenance' => Table::where('state', Table::STATE_MAINTENANCE)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tables,name'],
            'amount' => ['required', 'integer', 'min:1'],
            'state' => ['nullable', Rule::in(Table::states())],
        ], [
            'name.required' => 'El nombre de la mesa es obligatorio.',
            'name.unique' => 'Ya existe una mesa con ese nombre.',
            'amount.required' => 'La capacidad de la mesa es obligatoria.',
            'amount.integer' => 'La capacidad debe ser un número entero.',
            'amount.min' => 'La capacidad debe ser mayor a 0.',
        ]);

        Table::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'state' => $request->state,
        ]);

        return back()->with('success', 'Mesa registrada correctamente.');
    }

    public function update(Request $request, Table $table)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tables', 'name')->ignore($table->id),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'state' => ['nullable', Rule::in(Table::states())],
        ], [
            'name.required' => 'El nombre de la mesa es obligatorio.',
            'name.unique' => 'Ya existe una mesa con ese nombre.',
            'amount.required' => 'La capacidad de la mesa es obligatoria.',
            'amount.integer' => 'La capacidad debe ser un número entero.',
            'amount.min' => 'La capacidad debe ser mayor a 0.',
        ]);

        $table->update([
            'name' => $request->name,
            'amount' => $request->amount,
            'state' => $request->state,
        ]);

        return back()->with('success', 'Mesa actualizada correctamente.');
    }

    public function destroy(Table $table)
    {
        if ($table->details_reservations()->exists() || $table->sales_notes()->exists()) {
            return back()->with('error', 'No se puede eliminar esta mesa porque ya tiene reservas o ventas relacionadas. Puedes colocarla como Inactiva.');
        }

        $table->delete();

        return back()->with('success', 'Mesa eliminada correctamente.');
    }

    private function paginate($query, string $perPage)
    {
        if ($perPage === 'all') {
            $total = max($query->count(), 1);

            return $query->paginate($total);
        }

        return $query->paginate((int) $perPage);
    }
}
