<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'player_name', 'num1', 'num2', 'correct_gcd', 'player_gcd', 'result'
    ];
}
