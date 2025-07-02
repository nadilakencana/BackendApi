<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class HistoryChat extends Model
{
    use HasFactory;
    protected $fillable = ['id_session', 'sender_type', 'message'];

    public function session()
    {
        return $this->belongsTo(SessionChats::class, 'id_session', 'id');
    }
}
