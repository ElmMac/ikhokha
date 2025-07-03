<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ikhokha_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('paylink_id')->unique();
            $table->string('description')->unique();
            $table->string('customer_email');
            $table->string('transaction_id')->unique(); // Maps to externalTransactionID from iKhokha
            $table->integer('amount')->unsigned();
            $table->string('currency', 10)->default('ZAR');
            $table->string('mode')->default('live');
            $table->string('status')->default('pending'); // pending, completed, failed, etc.
            $table->timestamp('webhook_received_at')->nullable();
            $table->string('webhook_signature')->nullable();
            $table->string('ik_app_id')->nullable();
            $table->timestamp('paid_at')->nullable(); // only filled on success
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ikhokha_payments');
    }
};
