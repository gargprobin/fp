<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $fillable = [
        'recipient_name',
        'recipient_phone',
        'recipient_role',
        'title',
        'message',
        'status',
    ];
}
