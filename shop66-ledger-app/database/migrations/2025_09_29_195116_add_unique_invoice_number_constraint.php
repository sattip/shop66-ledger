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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['store_id', 'invoice_number'], 'invoices_store_invoice_unique');
            $table->index(['store_id', 'invoice_date', 'status'], 'invoices_store_date_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_store_invoice_unique');
            $table->dropIndex('invoices_store_date_status_idx');
        });
    }
};
