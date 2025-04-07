<?php
session_start();


$dbPath = __DIR__ . '/../db/database.sqlite';
$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$pdo->exec("
    CREATE TABLE IF NOT EXISTS games (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        player_name TEXT NOT NULL,
        date DATETIME NOT NULL,
        number1 INTEGER NOT NULL,
        number2 INTEGER NOT NULL,
        user_answer INTEGER NOT NULL,
        correct_gcd INTEGER NOT NULL,
        is_correct BOOLEAN NOT NULL
    )
");

$action = $_GET['action'] ?? 'play';

if ($action === 'play') {
    if (!isset($_SESSION['current_numbers'])) {
        $num1 = rand(1, 100);
        $num2 = rand(1, 100);
        $gcd = computeGCD($num1, $num2);
        $_SESSION['current_numbers'] = compact('num1', 'num2', 'gcd');
    } else {
        $num1 = $_SESSION['current_numbers']['num1'];
        $num2 = $_SESSION['current_numbers']['num2'];
    }
    displayPlayForm($num1, $num2);
} elseif ($action === 'check' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handleFormSubmission($pdo);
} elseif ($action === 'view') {
    displayHistory($pdo);
} else {
    header('Location: index.php?action=play');
    exit;
}

function computeGCD($a, $b) {
    while ($b != 0) {
        $temp = $a % $b;
        $a = $b;
        $b = $temp;
    }
    return $a;
}

function displayPlayForm($num1, $num2) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Наибольший общий делитель</title>
    </head>
    <body>
        <h1>Найдите НОД чисел <?= $num1 ?> и <?= $num2 ?></h1>
        <form method="POST" action="index.php?action=check">
            <label>Ваше имя: <input type="text" name="player_name" required></label><br>
            <label>Ваш ответ: <input type="number" name="user_answer" required></label><br>
            <button type="submit">Проверить</button>
        </form>
        <p><a href="index.php?action=view">История игр</a></p>
    </body>
    </html>
    <?php
}

function handleFormSubmission($pdo) {
    $playerName = $_POST['player_name'] ?? '';
    $userAnswer = (int)($_POST['user_answer'] ?? 0);
    $currentNumbers = $_SESSION['current_numbers'] ?? null;

    if (!$currentNumbers) {
        exit('Ошибка: сессия не активна');
    }

    $isCorrect = $userAnswer === $currentNumbers['gcd'];
    $stmt = $pdo->prepare("INSERT INTO games (player_name, date, number1, number2, user_answer, correct_gcd, is_correct) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $playerName,
        date('Y-m-d H:i:s'),
        $currentNumbers['num1'],
        $currentNumbers['num2'],
        $userAnswer,
        $currentNumbers['gcd'],
        $isCorrect ? 1 : 0
    ]);
    unset($_SESSION['current_numbers']);
    displayResult($isCorrect, $currentNumbers['gcd']);
}

function displayResult($isCorrect, $correctGCD) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Результат</title>
    </head>
    <body>
        <h1><?= $isCorrect ? '✅ Правильно!' : '❌ Неправильно!' ?></h1>
        <p>Правильный НОД: <?= $correctGCD ?></p>
        <p><a href="index.php?action=play">Новая игра</a></p>
        <p><a href="index.php?action=view">История игр</a></p>
    </body>
    </html>
    <?php
}

function displayHistory($pdo) {
    $stmt = $pdo->query("SELECT * FROM games ORDER BY date DESC");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>История игр</title>
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <h1>История игр</h1>
        <table>
            <tr>
                <th>Имя игрока</th>
                <th>Дата</th>
                <th>Числа</th>
                <th>Ответ</th>
                <th>Результат</th>
            </tr>
            <?php foreach ($games as $game): ?>
            <tr>
                <td><?= htmlspecialchars($game['player_name']) ?></td>
                <td><?= $game['date'] ?></td>
                <td><?= $game['number1'] ?> и <?= $game['number2'] ?></td>
                <td><?= $game['user_answer'] ?> (Правильно: <?= $game['correct_gcd'] ?>)</td>
                <td><?= $game['is_correct'] ? '✅' : '❌' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><a href="index.php?action=play">Новая игра</a></p>
    </body>
    </html>
    <?php
}