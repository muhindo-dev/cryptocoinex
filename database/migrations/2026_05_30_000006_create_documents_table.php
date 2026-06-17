<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('doc_number')->unique();
            $table->string('title');
            $table->enum('category', [
                'notice_to_sue',
                'court_order',
                'affidavit',
                'power_of_attorney',
                'contract_agreement',
                'evidence',
                'police_report',
                'correspondence',
                'legal_opinion',
                'judgment',
                'land_title',
                'company_docs',
                'id_documents',
                'summons',
                'pleadings',
                'other',
            ])->default('other');
            $table->foreignId('case_id')->nullable()->constrained('legal_cases')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
