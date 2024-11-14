<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRtmpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rtmps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('name');
            $table->string('rtmp_url');
            $table->string('stream_key');
            $table->string('live_url');
            $table->string('server_name')->unique();
            $table->string('container_name')->unique();
            $table->integer('rtmp_port')->unique();
            $table->integer('http_port')->unique();
            $table->tinyInteger('status')->default(1)->comment('1->Active, 0->Inactive, 2->Pending');
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
        Schema::dropIfExists('rtmps');
    }
}
