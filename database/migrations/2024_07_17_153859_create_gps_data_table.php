<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGPSDataTable extends Migration
{
    public function up()
    {
        Schema::create('gps_data', function (Blueprint $table) {
            $table->id();
            $table->float('latitude', 10, 6);
            $table->float('longitude', 10, 6);
            $table->string('status');
            $table->timestamp('timestamp');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gps_data');
    }
}
