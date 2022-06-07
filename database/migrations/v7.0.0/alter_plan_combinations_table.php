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
        Schema::table(config('subby.tables.plan_combinations'), function (Blueprint $table) {
            $table->dropUnique('unique_plan_combination');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(config('subby.tables.plan_combinations'), function (Blueprint $table) {
            $table->unique(['plan_id', 'country', 'currency', 'invoice_period', 'invoice_interval'], 'unique_plan_combination');
        });
    }
};
