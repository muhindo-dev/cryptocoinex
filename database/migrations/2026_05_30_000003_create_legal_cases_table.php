<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'civil_litigation',
                'criminal_defense',
                'family_law',
                'land_property',
                'commercial_corporate',
                'employment_labour',
                'human_rights',
                'constitutional',
                'succession_probate',
                'debt_recovery',
                'immigration',
                'other',
            ])->default('other');
            $table->enum('status', ['pending', 'active', 'ongoing', 'closed', 'archived'])->default('pending');
            $table->enum('stage', [
                'intake',
                'investigation',
                'pre_trial',
                'mediation',
                'trial',
                'appeal',
                'settlement',
                'enforcement',
                'closed',
            ])->default('intake');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('main_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('filing_date');
            $table->date('closed_date')->nullable();

            // Court tracking
            $table->boolean('is_in_court')->default(false);
            $table->string('court_name')->nullable();
            $table->string('court_division')->nullable();
            $table->string('court_case_number')->nullable();
            $table->string('judge_name')->nullable();
            $table->date('next_hearing_date')->nullable();

            // Police tracking
            $table->boolean('is_at_police')->default(false);
            $table->string('police_station')->nullable();
            $table->string('police_ref_number')->nullable();
            $table->string('investigating_officer')->nullable();

            // Outcome (set when closing)
            $table->tinyInteger('score')->nullable()->comment('+1=win, 0=neutral, -1=lost');
            $table->text('closing_remarks')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};
