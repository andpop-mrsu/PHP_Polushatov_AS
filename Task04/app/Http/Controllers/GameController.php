<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private function gcd($a, $b)
    {
        while ($b != 0) {
            $t = $b;
            $b = $a % $b;
            $a = $t;
        }
        return abs($a);
    }

    public function createGame(Request $request)
    {
        $playerName = $request->input('player', 'Игрок');
        $num1 = rand(1, 100);
        $num2 = rand(1, 100);
        $correctGcd = $this->gcd($num1, $num2);

        $game = Game::create([
            'player_name' => $playerName,
            'num1' => $num1,
            'num2' => $num2,
            'correct_gcd' => $correctGcd,
        ]);

        return response()->json([
            'id' => $game->id,
            'num1' => $num1,
            'num2' => $num2
        ]);
    }

    public function makeStep(Request $request, $id)
    {
        $game = Game::findOrFail($id);
        $playerGcd = (int) $request->input('gcd');

        $result = ($playerGcd == $game->correct_gcd) ? 'Верно' : 'Неверно';
        $game->update([
            'player_gcd' => $playerGcd,
            'result' => $result,
        ]);

        return response()->json([
            'correct_gcd' => $game->correct_gcd,
            'player_gcd' => $playerGcd,
            'result' => $result
        ]);
    }

    public function getGames()
    {
        return response()->json(Game::orderBy('created_at', 'desc')->get());
    }

    public function showGamePage()
    {
        return view('game');
    }
}