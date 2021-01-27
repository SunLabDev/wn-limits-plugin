<?php namespace SunLab\Limits\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateLimitsTable extends Migration
{
    public function up()
    {
        Schema::create('sunlab_limits_limits', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();

            $table->string('code');
            $table->string('description')->nullable();

            $table->unique('code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sunlab_limits_limits');
    }
}
