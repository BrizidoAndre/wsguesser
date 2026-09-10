<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    public function game()
    {
        return $this->hasOne(Game::class, 'word_id');
    }
}
