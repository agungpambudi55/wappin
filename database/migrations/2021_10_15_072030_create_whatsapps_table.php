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
            $table->string('project_id')->nullable();
            $table->string('telephone')->nullable();
            $table->string('message_content')->nullable();
            $table->string('message_sent_at')->nullable();
            $table->string('message_delivered_at')->nullable();
            $table->string('message_read_at')->nullable();
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
