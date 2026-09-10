<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    public function word()
    {
        return $this->belongsTo(Word::class);
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'game_id');
    }
}
