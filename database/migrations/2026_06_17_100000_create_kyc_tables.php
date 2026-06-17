<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KYC (identity verification). A user must be approved before they can use any
 * real-money feature — going Live to trade, depositing, or withdrawing.
 *
 * users.kyc_status is the fast-path flag used for gating; kyc_submissions keeps
 * the full history of attempts (a user can be asked to redo and resubmit).
 *
 * Document photos are stored on the PRIVATE disk and only ever streamed through
 * an admin-gated route — they must never be publicly reachable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // unverified | pending | approved | declined | resubmit
            $table->string('kyc_status', 16)->default('unverified')->index()->after('role');
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_status');
        });

        Schema::create('kyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name', 150);
            $table->string('document_type', 32);     // passport | national_id | drivers_license | other
            $table->string('document_number', 80);
            $table->string('document_path', 255);     // private-disk path to the uploaded photo
            $table->text('message')->nullable();      // applicant's note
            // pending | approved | declined | resubmit
            $table->string('status', 16)->default('pending');
            $table->text('admin_note')->nullable();   // reviewer feedback (reason / what to redo)
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_submissions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kyc_status', 'kyc_verified_at']);
        });
    }
};
