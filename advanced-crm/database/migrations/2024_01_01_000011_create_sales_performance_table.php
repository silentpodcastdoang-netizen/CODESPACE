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
        Schema::create('sales_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0.00);
            $table->decimal('total_profit', 15, 2)->default(0.00);
            $table->integer('new_customers')->default(0);
            $table->decimal('target_revenue', 15, 2)->default(0.00);
            $table->decimal('achievement_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            // Unique constraint for user, period type, and date range
            $table->unique(['user_id', 'period_type', 'period_start', 'period_end']);

            // Indexes
            $table->index('user_id');
            $table->index('period_type');
            $table->index('period_start');
            $table->index('period_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_performance');
    }
};