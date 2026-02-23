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

        $reservations = Reservation::where('status', ReservationStatus::Confirmed)
            ->get();

        $marked = 0;
        foreach ($reservations as $reservation) {
            $slot = $reservation->reservation_date->copy()->setTimeFromTimeString(
                $reservation->reservation_time->format('H:i:s')
            );
            if ($slot->gte($cutoff)) {
                continue;
            }
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
