<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('sms_balance', 12, 4)->default(0)->after('overpaid_amount');
            $table->boolean('sms_low_balance_notified')->default(false)->after('sms_balance');
            $table->timestamp('sms_last_topup_at')->nullable()->after('sms_low_balance_notified');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['sms_balance', 'sms_low_balance_notified', 'sms_last_topup_at']);
        });
    }
};
