<?php namespace SunLab\Limits\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateLimitableTable extends Migration
{
    public function up()
    {
        Schema::create('sunlab_limits_limitables', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedInteger('maximum')->nullable();
            $table->unsignedInteger('limit_id');
            $table->unsignedInteger('limitable_id');
            $table->string('limitable_type');

            $table->unique(['limit_id', 'limitable_id', 'limitable_type'], 'sunlab_limits_limitables_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sunlab_limits_limitables');
    }
}
