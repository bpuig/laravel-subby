<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('subby.tables.plan_subscriptions'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('tag');
            $table->morphs('subscriber');
            $table->unsignedInteger('plan_id')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->decimal('price')->default('0.00');
            $table->string('currency', 3);
            $table->unsignedSmallInteger('trial_period')->default(0);
            $table->string('trial_interval')->default('day');
            $table->unsignedSmallInteger('grace_period')->default(0);
            $table->string('grace_interval')->default('day');
            $table->unsignedSmallInteger('invoice_period')->default(1);
            $table->string('invoice_interval')->default('month');
            $table->unsignedMediumInteger('tier')->default(0);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancels_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['tag', 'subscriber_id', 'subscriber_type'], 'unique_plan_subscription');
            $table->foreign('plan_id')->references('id')->on(config('subby.tables.plans'))
                ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('subby.tables.plan_subscriptions'));
    }
}
