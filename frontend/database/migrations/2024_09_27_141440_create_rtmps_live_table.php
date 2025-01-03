<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRtmpsLiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rtmp_lives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rtmp_id');
            $table->tinyInteger('is_block')->default(0)->comment('1->Blocked, 0->Open');
            $table->tinyInteger('status')->default(1)->comment('1->Active, 0->Inactive');
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
        Schema::dropIfExists('rtmp_lives');
    }
}
