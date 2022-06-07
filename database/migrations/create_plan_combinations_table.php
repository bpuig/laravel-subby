<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('subby.tables.plan_combinations'), function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('plan_id')->nullable();
            $table->string('tag')->unique();
            $table->char('country', 3);
            $table->char('currency', 3);
            $table->decimal('price')->default('0.00');
            $table->decimal('signup_fee')->default('0.00');
            $table->unsignedSmallInteger('invoice_period')->default(1);
            $table->string('invoice_interval')->default('month');
            $table->timestamps();

            $table->unique(['plan_id','country', 'currency', 'invoice_period', 'invoice_interval'], 'unique_plan_combination');

            $table->foreign('plan_id', 'plan_id_fk')->references('id')->on(config('subby.tables.plans'))->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('subby.tables.plan_combinations'));
    }
};
