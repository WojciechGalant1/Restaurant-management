<?php

namespace App\Events;

use App\Data\DashboardFeedPayload;
use App\Models\Invoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceIssued implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'InvoiceIssued';
    }

    public function broadcastWith(): array
    {
        $payload = new DashboardFeedPayload(
            type: 'invoice_issued',
            message: __('Invoice :number issued – :amount PLN', [
                'number' => $this->invoice->invoice_number,
                'amount' => number_format($this->invoice->amount, 2),
            ]),
            time: now()->format('H:i'),
            link: route('invoices.show', $this->invoice),
        );
        return $payload->toArray();
    }
}
