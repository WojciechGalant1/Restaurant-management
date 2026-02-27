<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Console\Command;

class MarkReservationNoShow extends Command
{
    protected $signature = 'reservations:mark-no-show {--grace=15 : Minutes after reservation time before marking no-show}';

    protected $description = 'Mark confirmed reservations as no-show when past reservation time plus grace period';

    public function __construct(
        private ReservationService $reservationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $graceMinutes = (int) $this->option('grace');
        $cutoff = now()->subMinutes($graceMinutes);

        // Find confirmed reservations that are past their scheduled time (reservation_date + reservation_time)
        // using a single query structure for clarity.
        $reservations = Reservation::where('status', ReservationStatus::Confirmed)
            ->where(function ($q) use ($cutoff) {
                // Case A: Date is in the past
                $q->whereDate('reservation_date', '<', $cutoff->toDateString())
                  // Case B: Date is today, but time is past cutoff
                  ->orWhere(function ($q2) use ($cutoff) {
                      $q2->whereDate('reservation_date', $cutoff->toDateString())
                         ->whereTime('reservation_time', '<', $cutoff->format('H:i:s'));
                  });
            })
            ->get();

        $marked = 0;
        foreach ($reservations as $reservation) {
            try {
                $this->reservationService->updateStatus($reservation, ReservationStatus::NoShow);
                $marked++;
                $this->line("Marked reservation #{$reservation->id} ({$reservation->customer_name}) as no-show.");
            } catch (\Throwable $e) {
                $this->warn("Could not mark reservation #{$reservation->id}: {$e->getMessage()}");
            }
        }

        if ($marked > 0) {
            $this->info("Marked {$marked} reservation(s) as no-show.");
        }

        return self::SUCCESS;
    }
}
