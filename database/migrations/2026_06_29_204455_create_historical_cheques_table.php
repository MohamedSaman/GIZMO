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
        Schema::create('historical_cheques', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['received', 'issued']);
            $table->string('party_name');
            $table->string('cheque_number');
            $table->string('bank_name');
            $table->date('cheque_date');
            $table->decimal('cheque_amount', 15, 2);
            $table->enum('status', ['complete', 'pending', 'return', 'cancelled'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_cheques');
    }
};
