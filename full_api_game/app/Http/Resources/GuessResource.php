<?php

namespace App\Http\Resources;

use App\Http\Helpers\GuessHelper;
use App\Models\Attempt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Attempt */
class GuessResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'word' => $this->word,
            'feedback' => GuessHelper::showGuessStatues($this->game->word->name, $this->word)
        ];
    }
}
