<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSocketsStatistics extends Model
{
    protected $table = 'websockets_statistics';
    
    protected $fillable = [
        'app_id',
        'peak_connection_count',
        'websocket_message_count',
        'api_message_count',
    ];

    protected $casts = [
        'peak_connection_count' => 'integer',
        'websocket_message_count' => 'integer',
        'api_message_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
