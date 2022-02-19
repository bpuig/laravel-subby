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
            $table->morphs('scheduleable', 'scheduleable');
            $table->dropForeign('plan_id_fk');
            $table->dropForeign('plan_subscription_fk');
            $table->dropUnique('unique_plan_subscription_keys');
        });

        app(config('subby.models.plan_subscription_schedule'))::query()->update([
            'scheduleable_type' => config('subby.models.plan'),
            'scheduleable_id' => \DB::raw('plan_id')
        ]);

        Schema::table(config('subby.tables.plan_subscription_schedules'), function (Blueprint $table) {
            $table->dropColumn(['service', 'plan_id']);
            $table->unique(['subscription_id', 'scheduleable_type', 'scheduleable_id', 'scheduled_at'], 'unique_plan_subscription_keys');
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
            $table->unsignedInteger('plan_id')->after('subscription_id');
            $table->string('service')->default('default')->after('plan_id');
            $table->dropUnique('unique_plan_subscription_keys');
        });

        app(config('subby.models.plan_subscription_schedule'))::update([
            'plan_id' => \DB::raw('scheduleable_id')
        ]);

        Schema::table(config('subby.tables.plan_subscription_schedules'), function (Blueprint $table) {
            $table->dropMorphs('scheduleable');
            $table->unique(['subscription_id', 'plan_id', 'scheduled_at'], 'unique_plan_subscription_keys');
            $table->foreign('plan_id', 'plan_id_fk')->references('id')->on(config('subby.tables.plans'))->onDelete('cascade')->onUpdate('cascade');
        });
    }
};
