<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRankDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rank_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('rank_id');
            $table->string('rank_name',30)->nullable();
            $table->integer('topup_amount')->default(0);
            $table->integer('firstline')->default(0);
            $table->integer('secondline')->default(0);
            $table->integer('teamsize')->default(0);
            $table->integer('direct_business')->default(0);
            $table->integer('direct_count')->default(0);
            $table->integer('cps_amount')->default(0);
            $table->smallInteger('leg_count')->default(0);
            $table->smallInteger('rank')->default(1);
            $table->smallInteger('status')->default(1)->comment('1 normal criteria 2 rank crteria');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rank_details');
    }
}
