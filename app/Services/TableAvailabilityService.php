<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TableAvailabilityService
{
    private const TOLERANCE_MINUTES = 15;

    public function availableForReservation(string $date, string $hour, ?int $ignoreReservationId = null): Collection
    {
        $blockedTableIds = $this->blockedTableIdsForReservation($date, $hour, $ignoreReservationId);

        return $this->physicalAvailableTablesQuery()
            ->whereNotIn('id', $blockedTableIds)
            ->orderBy('name')
            ->get();
    }

    public function availableForWaiterNow(): Collection
    {
        $now = now();

        $blockedTableIds = Reservation::query()
            ->whereDate('date', $now->toDateString())
            ->whereIn('state', Reservation::activeStates())
            ->where(function ($query) use ($now) {
                $query->where('state', Reservation::STATE_IN_PROCESS)
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery
                            ->whereIn('state', [
                                Reservation::STATE_PENDING,
                                Reservation::STATE_CONFIRMED,
                            ])
                            ->whereRaw(
                                '("date" + "hour") <= ? AND ("date" + "hour" + interval \'' . self::TOLERANCE_MINUTES . ' minutes\') >= ?',
                                [
                                    $now->format('Y-m-d H:i:s'),
                                    $now->format('Y-m-d H:i:s'),
                                ]
                            );
                    });
            })
            ->with('tables:id')
            ->get()
            ->flatMap(function ($reservation) {
                return $reservation->tables->pluck('id');
            })
            ->unique()
            ->values();

        return $this->physicalAvailableTablesQuery()
            ->whereNotIn('id', $blockedTableIds)
            ->orderBy('name')
            ->get();
    }

    public function reservationHasTableCollision(
        array $tableIds,
        string $date,
        string $hour,
        ?int $ignoreReservationId = null
    ): bool {
        $requestedStart = Carbon::parse($date . ' ' . $hour);
        $requestedEnd = $requestedStart->copy()->addMinutes(self::TOLERANCE_MINUTES);

        return Reservation::query()
            ->where('date', $date)
            ->whereIn('state', Reservation::activeStates())
            ->when($ignoreReservationId, function ($query) use ($ignoreReservationId) {
                $query->where('id', '!=', $ignoreReservationId);
            })
            ->where(function ($query) use ($requestedStart, $requestedEnd) {
                $query->whereRaw(
                    '("date" + "hour") < ? AND ("date" + "hour" + interval \'' . self::TOLERANCE_MINUTES . ' minutes\') > ?',
                    [
                        $requestedEnd->format('Y-m-d H:i:s'),
                        $requestedStart->format('Y-m-d H:i:s'),
                    ]
                );
            })
            ->whereHas('tables', function ($query) use ($tableIds) {
                $query->whereIn('tables.id', $tableIds);
            })
            ->exists();
    }

    public function blockedTableIdsForReservation(string $date, string $hour, ?int $ignoreReservationId = null): Collection
    {
        $requestedStart = Carbon::parse($date . ' ' . $hour);
        $requestedEnd = $requestedStart->copy()->addMinutes(self::TOLERANCE_MINUTES);

        return Reservation::query()
            ->where('date', $date)
            ->whereIn('state', Reservation::activeStates())
            ->when($ignoreReservationId, function ($query) use ($ignoreReservationId) {
                $query->where('id', '!=', $ignoreReservationId);
            })
            ->where(function ($query) use ($requestedStart, $requestedEnd) {
                $query->whereRaw(
                    '("date" + "hour") < ? AND ("date" + "hour" + interval \'' . self::TOLERANCE_MINUTES . ' minutes\') > ?',
                    [
                        $requestedEnd->format('Y-m-d H:i:s'),
                        $requestedStart->format('Y-m-d H:i:s'),
                    ]
                );
            })
            ->with('tables:id')
            ->get()
            ->flatMap(function ($reservation) {
                return $reservation->tables->pluck('id');
            })
            ->unique()
            ->values();
    }

    private function physicalAvailableTablesQuery(): Builder
    {
        return Table::query()
            ->where(function ($query) {
                $query->whereNull('state')
                    ->orWhere('state', Table::STATE_AVAILABLE);
            });
    }
}
