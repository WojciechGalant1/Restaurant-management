<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('bill_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        foreach (DB::table('invoices')->get() as $invoice) {
            $orderId = $invoice->order_id;
            $billId = DB::table('bills')->insertGetId([
                'order_id' => $orderId,
                'status' => 'paid',
                'total_amount' => $invoice->amount,
                'tip_amount' => null,
                'paid_at' => $invoice->issued_at,
                'created_at' => $invoice->created_at,
                'updated_at' => $invoice->updated_at,
            ]);
            DB::table('payments')->insert([
                'bill_id' => $billId,
                'amount' => $invoice->amount,
                'method' => $invoice->payment_method,
                'created_at' => $invoice->issued_at,
                'updated_at' => $invoice->issued_at,
            ]);
            DB::table('invoices')->where('id', $invoice->id)->update(['bill_id' => $billId]);
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->dropColumn('payment_method');
        });

    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('bill_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['cash', 'card', 'online'])->default('card')->after('customer_name');
        });

        foreach (DB::table('invoices')->get() as $invoice) {
            $bill = DB::table('bills')->find($invoice->bill_id);
            if ($bill) {
                $payment = DB::table('payments')->where('bill_id', $invoice->bill_id)->first();
                DB::table('invoices')->where('id', $invoice->id)->update([
                    'order_id' => $bill->order_id,
                    'payment_method' => $payment?->method ?? 'card',
                ]);
            }
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['bill_id']);
            $table->dropColumn('bill_id');
        });
    }
};
