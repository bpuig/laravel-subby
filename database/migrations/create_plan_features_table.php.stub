<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('subby.tables.plan_features'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->string('tag');
            $table->unsignedInteger('plan_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('value');
            $table->unsignedSmallInteger('resettable_period')->default(0);
            $table->string('resettable_interval')->default('month');
            $table->unsignedMediumInteger('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->unique(['tag', 'plan_id']);
            $table->foreign('plan_id')->references('id')->on(config('subby.tables.plans'))
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('subby.tables.plan_features'));
    }
}
