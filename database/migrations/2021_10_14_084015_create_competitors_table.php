<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompetitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->string('urn_competitor')->default('')->comment('選手編號');
            $table->string('name')->default('')->comment('名稱');
            $table->string('name_zht')->default('')->comment('中文名稱');
            $table->string('country')->default('')->comment('國家');
            $table->string('gender')->default('')->comment('性別');
            $table->bigInteger('rank')->default(0)->comment('排名');
            $table->string('image', 1024)->default('')->comment('選手圖');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competitors');
    }
}
