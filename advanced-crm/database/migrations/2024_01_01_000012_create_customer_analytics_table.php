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
        Schema::create('customer_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0.00);
            $table->decimal('average_order_value', 15, 2)->default(0.00);
            $table->date('last_order_date')->nullable();
            $table->integer('loyalty_score')->default(0);
            $table->timestamps();

            // Unique constraint for company, period type, and date range
            $table->unique(['company_id', 'period_type', 'period_start', 'period_end']);

            // Indexes
            $table->index('company_id');
            $table->index('period_type');
            $table->index('period_start');
            $table->index('period_end');
            $table->index('last_order_date');
            $table->index('loyalty_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_analytics');
    }
};