<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class SessionChats extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'string';
    protected $fillable = ['id', 'title', 'started_at', 'ended_at','user_id'];

    public function messages()
    {
        return $this->hasMany(HistoryChat::class, 'id_session', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
