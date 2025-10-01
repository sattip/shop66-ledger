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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 18, 4);
            $table->decimal('actual', 18, 4)->default(0);
            $table->string('currency_code', 3)->nullable();
            $table->string('status', 32)->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['store_id', 'category_id', 'account_id', 'period_start', 'period_end'],
                'budgets_period_account_unique'
            );
            $table->index(['store_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
