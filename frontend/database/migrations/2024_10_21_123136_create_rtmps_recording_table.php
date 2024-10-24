<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRtmpsRecordingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rtmp_recordings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rtmp_id');
            $table->text('recording_url')->nullable();
            $table->dateTime('recording_datetime');
            $table->text('recording_path');
            $table->tinyInteger('status')->dafault(0)->comment('1->Active, 0->Inactive');
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
        Schema::dropIfExists('rtmp_recordings');
    }
}
