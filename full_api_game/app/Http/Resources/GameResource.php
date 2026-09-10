<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Game */
class GameResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'gameId' => $this->id,
            'status' => $this->status ?? 'playing',
            'attemptsUsed' => $this->attemptsUsed ?? 0,
            'attemptsRemaining' => $this->attemptsRemaining ?? 6
        ];
    }
}
