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
        Schema::create(config('subby.tables.plan_subscription_schedules'), function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('subscription_id');
            $table->morphs('scheduleable', 'scheduleable_index');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();

            $table->unique(['subscription_id', 'scheduleable_type', 'scheduleable_id', 'scheduled_at'], 'unique_plan_subscription_keys');

            $table->foreign('subscription_id', 'plan_subscription_fk')->references('id')->on(config('subby.tables.plan_subscriptions'))->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('subby.tables.plan_subscription_schedules'));
    }
};
