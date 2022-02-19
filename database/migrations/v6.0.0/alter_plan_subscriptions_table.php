<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(config('subby.tables.plan_subscriptions'), function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('free')->after('invoice_interval');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(config('subby.tables.plan_subscriptions'), function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
