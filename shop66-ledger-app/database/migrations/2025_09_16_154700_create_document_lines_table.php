<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_line_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('line_number')->default(1);
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('unit_price', 18, 4)->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('total', 18, 4)->default(0);
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('status', 32)->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'line_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_lines');
    }
};
