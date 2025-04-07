<?php
namespace PHP_Polushatov_AS\GCD\Controller;

use function PHP_Polushatov_AS\GCD\View\showWelcome;
use function PHP_Polushatov_AS\GCD\View\askQuestion;
use function PHP_Polushatov_AS\GCD\View\showResult;

function gcd($a, $b) {
    return $b ? gcd($b, $a % $b) : $a;
}

function startGame() {
    showWelcome();
    $a = rand(1, 100);
    $b = rand(1, 100);
    $correctAnswer = gcd($a, $b);
    $userAnswer = (int)askQuestion("Найдите НОД чисел $a и $b");
    showResult($userAnswer === $correctAnswer, $correctAnswer);
}
?>