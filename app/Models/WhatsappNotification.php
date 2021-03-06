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
        'project_id',
        'telephone',
        'content',
        'sent_at',
        'delivered_at',
        'read_at'
    ];
}
