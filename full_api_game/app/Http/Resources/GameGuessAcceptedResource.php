<?php

namespace App\Http\Resources;

use App\Http\Helpers\GuessHelper;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Game */
class GameGuessAcceptedResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'status' => $this->status,
            'attemptsUsed' => $this->attemptsUsed ?? 0,
            'attemptsRemaining' => $this->attemptsRemaining,
            'secretWord' => $this->when($this->status === 'lost', function () {
                return $this->word->name;
            }),
            'feedback' => GuessHelper::showGuessStatues($this->word->name, $this->attempts->last()->word),
            // only shows the message if the status is different from playing
            'message' => $this->when($this->status !== 'playing', function () {
                return $this->status === 'won' ? 'Congratulations! You guessed the word.' : 'Game over.';
            })
        ];
    }
}
