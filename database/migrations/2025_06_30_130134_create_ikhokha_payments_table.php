<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ikhokha_payments', function (Blueprint $table) {
            $table->id();

            // Link to your users table
            // $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // iKhokha Paylink ID
            $table->string('paylink_id')->unique()->index();

            // Transaction reference (externalTransactionID)
            $table->string('transaction_id')->unique()->index();

            // Human readable description of the payment
            $table->string('description');

            // Optional customer details
            $table->string('customer_email')->nullable();

            // Amount details
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('ZAR');

            // live | sandbox
            $table->string('mode')->default('live')->index();

            // pending | completed | failed | cancelled | expired | reversed
            $table->string('status')->default('pending')->index();

            // Optional additional data from iKhokha API
            $table->string('payment_url')->nullable();
            $table->json('metadata')->nullable();

            // Webhook integrity
            $table->json('webhook_payload')->nullable();
            $table->string('webhook_signature')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ikhokha_payments');
    }
};
