<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->string('receipt_number')->unique()->nullable();
            $table->enum('type', ['income', 'expense'])->default('income');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->text('details')->nullable();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('case_id')->nullable()->constrained('legal_cases')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('financial_period_id')->nullable()->constrained('financial_periods')->nullOnDelete();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'mobile_money'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->date('transaction_date');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
