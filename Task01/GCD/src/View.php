<?php
namespace PHP_Polushatov_AS\GCD\View;

use function cli\line;
use function cli\prompt;

function showWelcome() {
    line("Добро пожаловать в игру 'Наибольший общий делитель'!");
}

function askQuestion(string $question): string {
    return prompt($question);
}

function showResult(bool $isCorrect, int $answer) {
    if ($isCorrect) {
        line("Правильно! Ответ: $answer");
    } else {
        line("Неверно. Правильный ответ: $answer");
    }
}
?>