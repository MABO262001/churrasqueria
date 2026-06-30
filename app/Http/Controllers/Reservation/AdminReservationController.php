<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Services\TableAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminReservationController extends Controller
{
    public function index(Request $request, TableAvailabilityService $availabilityService)
    {
        $perPage = $request->input('per_page', '10');

        $query = Reservation::query()
            ->with([
                'admin:id,name,email',
                'client:id,name,email',
                'client.profile:id,users_id,ci,name,last_name,telephone',
                'tables:id,name,amount,state',
            ])
            ->latest();

        if ($request->filled('search')) {
            $query->where(function ($subQuery) use ($request) {
                $subQuery->where('descriptions', 'ILIKE', '%' . $request->search . '%')
                    ->orWhereHas('client', function ($clientQuery) use ($request) {
                        $clientQuery->where('name', 'ILIKE', '%' . $request->search . '%')
                            ->orWhere('email', 'ILIKE', '%' . $request->search . '%')
                            ->orWhereHas('profile', function ($profileQuery) use ($request) {
                                $profileQuery
                                    ->whereRaw('CAST(ci AS TEXT) ILIKE ?', ['%' . $request->search . '%'])
                                    ->orWhere('name', 'ILIKE', '%' . $request->search . '%')
                                    ->orWhere('last_name', 'ILIKE', '%' . $request->search . '%');
                            });
                    });
            });
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $reservations = $this->paginate($query, $perPage)
            ->withQueryString()
            ->through(fn ($reservation) => $this->reservationPayload($reservation));

        $availableNowTables = $this->tablesPayload(
            $availabilityService->availableForWaiterNow()
        );

        return Inertia::render('Reservations/Admin/Index', [
            'reservations' => $reservations,
            'states' => Reservation::states(),
            'availableNowTables' => $availableNowTables,
            'filters' => [
                'search' => $request->input('search', ''),
                'state' => $request->input('state', ''),
                'date' => $request->input('date', ''),
                'per_page' => $perPage,
            ],
            'stats' => [
                'total' => Reservation::count(),
                'pending' => Reservation::where('state', Reservation::STATE_PENDING)->count(),
                'confirmed' => Reservation::where('state', Reservation::STATE_CONFIRMED)->count(),
                'in_process' => Reservation::where('state', Reservation::STATE_IN_PROCESS)->count(),
                'available_now' => count($availableNowTables),
            ],
            'today' => now()->toDateString(),
            'currentTime' => now()->format('H:i'),
        ]);
    }

    public function searchClients(Request $request)
    {
        $search = trim($request->input('search', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $clients = User::role('Cliente')
            ->with('profile:id,users_id,ci,name,last_name,telephone')
            ->select('id', 'name', 'email')
            ->where(function ($query) use ($search) {
                $query->where('name', 'ILIKE', '%' . $search . '%')
                    ->orWhere('email', 'ILIKE', '%' . $search . '%')
                    ->orWhereHas('profile', function ($profileQuery) use ($search) {
                        $profileQuery
                            ->whereRaw('CAST(ci AS TEXT) ILIKE ?', ['%' . $search . '%'])
                            ->orWhere('name', 'ILIKE', '%' . $search . '%')
                            ->orWhere('last_name', 'ILIKE', '%' . $search . '%');
                    });
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'ci' => $client->profile?->ci,
                    'profile_name' => $client->profile?->name,
                    'last_name' => $client->profile?->last_name,
                    'telephone' => $client->profile?->telephone,
                    'label' => trim(
                        ($client->profile?->name ?: $client->name) . ' ' . ($client->profile?->last_name ?: '')
                    ),
                ];
            })
            ->values();

        return response()->json($clients);
    }

    public function store(Request $request, TableAvailabilityService $availabilityService)
    {
        $request->validate([
            'descriptions' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'hour' => ['required', 'date_format:H:i'],
            'state' => ['required', Rule::in(Reservation::states())],
            'users_cliente_id' => ['required', 'exists:users,id'],
            'tables' => ['required', 'array', 'min:1'],
            'tables.*' => ['required', 'exists:tables,id'],
        ], $this->messages());

        if (in_array($request->state, Reservation::activeStates())) {
            $reservationDateTime = Carbon::parse($request->date . ' ' . $request->hour);

            if ($reservationDateTime->lt(now())) {
                return back()->withErrors([
                    'date' => 'La fecha y hora de una reserva activa no puede ser menor a la actual.',
                ])->withInput();
            }

            if ($availabilityService->reservationHasTableCollision($request->tables, $request->date, $request->hour)) {
                return back()->withErrors([
                    'tables' => 'Una o más mesas seleccionadas ya están reservadas en ese horario.',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            $reservation = Reservation::create([
                'descriptions' => $request->descriptions,
                'date' => $request->date,
                'hour' => $request->hour,
                'state' => $request->state,
                'users_id' => auth()->id(),
                'users_cliente_id' => $request->users_cliente_id,
            ]);

            $reservation->tables()->sync($request->tables);
        });

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reserva registrada correctamente.');
    }

    public function update(Request $request, Reservation $reservation, TableAvailabilityService $availabilityService)
    {
        $request->validate([
            'descriptions' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'hour' => ['required', 'date_format:H:i'],
            'state' => ['required', Rule::in(Reservation::states())],
            'users_cliente_id' => ['required', 'exists:users,id'],
            'tables' => ['required', 'array', 'min:1'],
            'tables.*' => ['required', 'exists:tables,id'],
        ], $this->messages());

        if (in_array($request->state, Reservation::activeStates())) {
            $reservationDateTime = Carbon::parse($request->date . ' ' . $request->hour);

            if ($reservationDateTime->lt(now())) {
                return back()->withErrors([
                    'date' => 'No se puede dejar una reserva activa con fecha y hora pasada.',
                ])->withInput();
            }

            if ($availabilityService->reservationHasTableCollision(
                $request->tables,
                $request->date,
                $request->hour,
                $reservation->id
            )) {
                return back()->withErrors([
                    'tables' => 'Una o más mesas seleccionadas ya están reservadas en ese horario.',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($request, $reservation) {
            $reservation->update([
                'descriptions' => $request->descriptions,
                'date' => $request->date,
                'hour' => $request->hour,
                'state' => $request->state,
                'users_cliente_id' => $request->users_cliente_id,
            ]);

            $reservation->tables()->sync($request->tables);
        });

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reserva actualizada correctamente.');
    }

    public function changeState(Request $request, Reservation $reservation, TableAvailabilityService $availabilityService)
    {
        $request->validate([
            'state' => ['required', Rule::in(Reservation::states())],
        ]);

        if (in_array($request->state, Reservation::activeStates())) {
            $reservationDateTime = Carbon::parse($reservation->date->format('Y-m-d') . ' ' . $reservation->hour);

            if ($reservationDateTime->lt(now())) {
                return back()->with('error', 'No se puede activar una reserva con fecha y hora pasada.');
            }

            $tableIds = $reservation->tables()->pluck('tables.id')->toArray();

            if ($availabilityService->reservationHasTableCollision(
                $tableIds,
                $reservation->date->format('Y-m-d'),
                substr($reservation->hour, 0, 5),
                $reservation->id
            )) {
                return back()->with('error', 'No se puede cambiar el estado porque una o más mesas ya están ocupadas en ese horario.');
            }
        }

        $reservation->update([
            'state' => $request->state,
        ]);

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Estado de la reserva actualizado correctamente.');
    }

    public function availableTables(Request $request, TableAvailabilityService $availabilityService)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'hour' => ['required', 'date_format:H:i'],
            'ignore_reservation_id' => ['nullable', 'integer'],
        ]);

        $tables = $this->tablesPayload(
            $availabilityService->availableForReservation(
                $request->date,
                $request->hour,
                $request->ignore_reservation_id
            )
        );

        return response()->json($tables);
    }

    public function availableNowTables(TableAvailabilityService $availabilityService)
    {
        return response()->json(
            $this->tablesPayload($availabilityService->availableForWaiterNow())
        );
    }

    private function reservationPayload(Reservation $reservation): array
    {
        return [
            'id' => $reservation->id,
            'descriptions' => $reservation->descriptions,
            'date' => optional($reservation->date)->format('Y-m-d'),
            'date_formatted' => optional($reservation->date)->format('d/m/Y'),
            'hour' => substr($reservation->hour, 0, 5),
            'state' => $reservation->state,
            'users_id' => $reservation->users_id,
            'users_cliente_id' => $reservation->users_cliente_id,
            'admin' => $reservation->admin,
            'client' => $reservation->client ? [
                'id' => $reservation->client->id,
                'name' => $reservation->client->name,
                'email' => $reservation->client->email,
                'ci' => $reservation->client->profile?->ci,
                'profile_name' => $reservation->client->profile?->name,
                'last_name' => $reservation->client->profile?->last_name,
                'telephone' => $reservation->client->profile?->telephone,
                'label' => trim(
                    ($reservation->client->profile?->name ?: $reservation->client->name) . ' ' . ($reservation->client->profile?->last_name ?: '')
                ),
            ] : null,
            'tables' => $this->tablesPayload($reservation->tables),
            'created_at' => optional($reservation->created_at)->format('d/m/Y H:i'),
        ];
    }

    private function tablesPayload($tables): array
    {
        return $tables
            ->map(fn ($table) => [
                'id' => $table->id,
                'name' => $table->name,
                'amount' => $table->amount,
                'state' => $table->state,
            ])
            ->values()
            ->toArray();
    }

    private function paginate($query, string $perPage)
    {
        if ($perPage === 'all') {
            $total = max($query->count(), 1);

            return $query->paginate($total);
        }

        return $query->paginate((int) $perPage);
    }

    private function messages(): array
    {
        return [
            'descriptions.required' => 'La descripción de la reserva es obligatoria.',
            'date.required' => 'La fecha de la reserva es obligatoria.',
            'hour.required' => 'La hora de la reserva es obligatoria.',
            'hour.date_format' => 'La hora debe tener el formato HH:MM.',
            'users_cliente_id.required' => 'Debe seleccionar un cliente.',
            'tables.required' => 'Debe seleccionar al menos una mesa.',
            'tables.min' => 'Debe seleccionar al menos una mesa.',
        ];
    }
}
