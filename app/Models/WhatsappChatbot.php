<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappChatbot extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'wa_id',
        'wa_name',
        'content',
        'sent_at',
        'delivered_at',
        'read_at',
        'deleted_at',
        'failed_at',
        'error_code',
        'error_title',
        'error_detail',
        'error_href'
    ];
}
