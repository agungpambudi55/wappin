<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'client_id',
        'client_name',
        'project_id',
        'project_name',
        'sender_number',
        'status_messages',
        'message_content',
        'environment',
        'timestamp',
        'callback_type'
    ];
}
