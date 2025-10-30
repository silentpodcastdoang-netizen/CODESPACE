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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_decision_maker')->default(false);
            $table->date('date_of_birth')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained()->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index('email');
            $table->index('phone');
            $table->index('mobile');
            $table->index('is_primary');
            $table->index('is_decision_maker');
            $table->index('created_by');
            $table->fullText(['first_name', 'last_name', 'position', 'department']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};