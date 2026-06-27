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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
            $table->unsignedBigInteger('customer_id')->nullable()->after('supplier_id');
            $table->date('valid_date')->nullable()->after('order_date');

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->dropColumn('valid_date');
            $table->unsignedBigInteger('supplier_id')->nullable(false)->change();
        });
    }
};
