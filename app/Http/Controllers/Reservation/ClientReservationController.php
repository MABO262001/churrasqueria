<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\TableAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClientReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::query()
            ->with(['tables:id,name,amount,state', 'admin:id,name,email'])
            ->where('users_cliente_id', auth()->id())
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($reservation) => [
                'id' => $reservation->id,
                'descriptions' => $reservation->descriptions,
                'date' => optional($reservation->date)->format('Y-m-d'),
                'date_formatted' => optional($reservation->date)->format('d/m/Y'),
                'hour' => substr($reservation->hour, 0, 5),
                'state' => $reservation->state,
                'admin' => $reservation->admin,
                'tables' => $reservation->tables->map(fn ($table) => [
                    'id' => $table->id,
                    'name' => $table->name,
                    'amount' => $table->amount,
                    'state' => $table->state,
                ])->values(),
                'created_at' => optional($reservation->created_at)->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Reservations/Client/Index', [
            'reservations' => $reservations,
            'today' => now()->toDateString(),
            'currentTime' => now()->format('H:i'),
        ]);
    }

    public function store(Request $request, TableAvailabilityService $availabilityService)
    {
        $request->validate([
            'descriptions' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'hour' => ['required', 'date_format:H:i'],
            'tables' => ['required', 'array', 'min:1'],
            'tables.*' => ['required', 'exists:tables,id'],
        ], [
            'descriptions.required' => 'Debe escribir una descripción para la reserva.',
            'date.required' => 'Debe seleccionar una fecha.',
            'hour.required' => 'Debe seleccionar una hora.',
            'hour.date_format' => 'La hora debe tener el formato HH:MM.',
            'tables.required' => 'Debe seleccionar al menos una mesa.',
            'tables.min' => 'Debe seleccionar al menos una mesa.',
        ]);

        $reservationDateTime = Carbon::parse($request->date . ' ' . $request->hour);

        if ($reservationDateTime->lt(now())) {
            return back()->withErrors([
                'date' => 'La fecha y hora de la reserva no puede ser menor a la fecha y hora actual.',
            ])->withInput();
        }

        if ($availabilityService->reservationHasTableCollision(
            $request->tables,
            $request->date,
            $request->hour
        )) {
            return back()->withErrors([
                'tables' => 'Una o más mesas seleccionadas ya están reservadas en ese horario.',
            ])->withInput();
        }

        DB::transaction(function () use ($request) {
            $reservation = Reservation::create([
                'descriptions' => $request->descriptions,
                'date' => $request->date,
                'hour' => $request->hour,
                'state' => Reservation::STATE_PENDING,
                'users_id' => auth()->id(),
                'users_cliente_id' => auth()->id(),
            ]);

            $reservation->tables()->sync($request->tables);
        });

        return redirect()
            ->route('client.reservations.index')
            ->with('success', 'Reserva registrada correctamente. Está pendiente de confirmación.');
    }

    public function cancel(Reservation $reservation)
    {
        if ($reservation->users_cliente_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($reservation->state, [
            Reservation::STATE_COMPLETED,
            Reservation::STATE_CANCELLED,
            Reservation::STATE_REJECTED,
        ])) {
            return redirect()
                ->route('client.reservations.index')
                ->with('error', 'Esta reserva ya no se puede cancelar.');
        }

        $reservationDateTime = Carbon::parse($reservation->date->format('Y-m-d') . ' ' . $reservation->hour);

        if ($reservationDateTime->lt(now())) {
            return redirect()
                ->route('client.reservations.index')
                ->with('error', 'No puedes cancelar una reserva con fecha y hora pasada.');
        }

        $reservation->update([
            'state' => Reservation::STATE_CANCELLED,
        ]);

        return redirect()
            ->route('client.reservations.index')
            ->with('success', 'Reserva cancelada correctamente.');
    }

    public function availableTables(Request $request, TableAvailabilityService $availabilityService)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'hour' => ['required', 'date_format:H:i'],
        ]);

        $reservationDateTime = Carbon::parse($request->date . ' ' . $request->hour);

        if ($reservationDateTime->lt(now())) {
            return response()->json([
                'message' => 'La fecha y hora no puede ser menor a la actual.',
                'tables' => [],
            ], 422);
        }

        $tables = $availabilityService
            ->availableForReservation($request->date, $request->hour)
            ->map(fn ($table) => [
                'id' => $table->id,
                'name' => $table->name,
                'amount' => $table->amount,
                'state' => $table->state,
            ])
            ->values();

        return response()->json($tables);
    }
}
