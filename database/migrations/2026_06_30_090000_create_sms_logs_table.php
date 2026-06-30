<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('phone', 20);
            $table->text('message');
            $table->integer('sms_parts')->default(1); // number of SMS parts (160 chars each)
            $table->decimal('cost_per_sms', 10, 4)->default(0);
            $table->decimal('total_cost', 10, 4)->default(0);
            $table->boolean('double_charged')->default(false); // was balance <= 0 at send time?
            $table->decimal('balance_before', 10, 4)->default(0);
            $table->decimal('balance_after', 10, 4)->default(0);
            $table->enum('type', ['invoice', 'alert', 'custom', 'low_balance'])->default('custom');
            $table->enum('status', ['sent', 'failed', 'skipped'])->default('sent');
            $table->string('api_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
