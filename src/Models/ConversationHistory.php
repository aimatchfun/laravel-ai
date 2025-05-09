<?php

namespace AIMatchFun\LaravelAI\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationHistory extends Model
{
    protected $table = 'laravelai_conversation_histories';

    protected $fillable = [
        'conversation_id',
        'provider',
        'model',
        'role',
        'content',
    ];
} 