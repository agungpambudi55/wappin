<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappChatbotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whatsapp_chatbots', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable();

            $table->string('wa_id')->nullable();
            $table->string('wa_name')->nullable();

            $table->text('content')->nullable();

            $table->dateTime('sent_at')->nullable();      // Message received by WhatsApp server (one checkmark)
            $table->dateTime('delivered_at')->nullable(); // Message delivered to recipient (two checkmarks)
            $table->dateTime('read_at')->nullable();      // Message read by recipient (two blue checkmarks)
            $table->dateTime('deleted_at')->nullable();   // Message deleted by the user (text 'This message was deleted')
            $table->dateTime('failed_at')->nullable();    // Message failed to send (red error triangle)

            $table->string('error_code')->nullable();
            $table->text('error_title')->nullable();
            $table->text('error_detail')->nullable();     // Optional, error details provided if available/applicable 
            $table->string('error_href')->nullable();     // Optional, location for error detail

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
        Schema::dropIfExists('whatsapp_chatbots');
    }
}
