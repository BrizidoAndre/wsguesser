<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameGuessAcceptedResource;
use App\Http\Resources\GameGuessResource;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Word;
use Illuminate\Http\Request;

class GameController extends Controller
{
    // creates a new game instance
    public function game()
    {
        // get a random word in the database
        $word = Word::inRandomOrder()->first();
        $game = $word->game()->create([
            'attemptsRemaining' => 6,
        ]);
        return GameResource::make($game);
    }

    // return the status of each attempt
    public function show(Game $game)
    {
        $game->load('attempts');
        return GameGuessResource::make($game);
    }

    // receives a word text and show the right status for each letter
    // it ends the game if there is no more attempts
    // or if the user guess the word correctly
    public function guess(Request $request, Game $game)
    {
        if ($game->status !== 'playing') {
            return response()->json(['message' => 'This game has already been completed.'], 409);
        }
        $request->validate([
            'word' => ['required', 'string', 'size:5', 'exists:words,name']
        ], [
            'word.required' => 'Words must contain exactly 5 letters.',
            'word.size' => 'Words must contain exactly 5 letters.',
            'word.exists' => 'The submitted word does not exist in the dictionary.'
        ]);
        $game->attempts()->create([
            'word' => $request->input('word'),
        ]);
        $attemptsUsed = $game->attemptsUsed + 1;
        $attemptsRemaining = $game->attemptsRemaining - 1;
        $status = 'playing';
        if ($request->input('word') === $game->word->name) {
            $status = 'won';
        } else if ($attemptsRemaining <= 0) {
            $status = 'lost';
        }
        $game->update([
            'attemptsUsed' => $attemptsUsed,
            'attemptsRemaining' => $attemptsRemaining,
            'status' => $status,
        ]);
        // load the attempts for each game
        // max of 5 attempts
        $game->load('attempts');
        return GameGuessAcceptedResource::make($game);
    }


}
