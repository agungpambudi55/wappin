<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapps', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->string('project_id')->nullable();
            $table->string('project_name')->nullable();
            $table->string('sender_number')->nullable();
            $table->string('status_messages')->nullable();
            $table->string('message_content')->nullable();
            $table->string('environment')->nullable();
            $table->string('timestamp')->nullable();
            $table->string('callback_type')->nullable();
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
        Schema::dropIfExists('whatsapps');
    }
}
