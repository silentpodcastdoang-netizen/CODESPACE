<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('source', [
                'website', 'referral', 'cold_call', 'email', 'social_media',
                'trade_show', 'existing_customer', 'other'
            ]);
            $table->enum('status', [
                'new', 'contacted', 'qualified', 'proposal',
                'negotiation', 'closed_won', 'closed_lost'
            ])->default('new');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->integer('probability')->default(0);
            $table->date('expected_close_date')->nullable();
            $table->foreignId('assigned_to')->constrained()->onDelete('restrict');
            $table->foreignId('created_by')->constrained()->onDelete('restrict');
            $table->date('closed_date')->nullable();
            $table->text('closed_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index('contact_id');
            $table->index('source');
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('created_by');
            $table->index('expected_close_date');
            $table->index('closed_date');
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};