<?php

namespace App\Http\Helpers;

use Illuminate\Support\Str;

class GuessHelper
{

    /**
     * based on two words its guesses the status of the word based on its presence and position
     * in other words
     * - absent -> when the letter is *not* present
     * - correct -> when the letter is present and in the right order
     * - present -> when the letter is present but on the wrong order
     * @param $correct
     * @param $word
     * @return array
     */
    public static function showGuessStatues($correct, $word)
    {
        $guessed = [];
        for ($j = 0; $j < 5; $j++) {
            $letterRight = $correct[$j];
            $letterGuessed = $word[$j];
            $status = 'absent';
            if ($letterRight === $letterGuessed) {
                $status = 'correct';
            } else if (Str::contains($correct, $letterGuessed)) {
                $status = 'present';
            }
            $guessed[] = [
                'letter' => $letterGuessed,
                'status' => $status,
            ];
        }
        return $guessed;
    }
}
