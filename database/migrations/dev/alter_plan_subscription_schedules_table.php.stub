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
        Schema::table(config('subby.tables.plan_subscription_schedules'), function (Blueprint $table) {
            $table->dropColumn('service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(config('subby.tables.plan_subscription_schedules'), function (Blueprint $table) {
            $table->string('service')->default('default')->after('plan_id');

        });
    }
};
