<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$dbPath = __DIR__ . '/../db/game.db';
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("
    CREATE TABLE IF NOT EXISTS games (
        id INTEGER PRIMARY KEY,
        player_name TEXT,
        num1 INTEGER,
        num2 INTEGER,
        correct_gcd INTEGER,
        player_gcd INTEGER,
        result TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
");

function gcd($a, $b) {
    while ($b != 0) {
        $t = $b;
        $b = $a % $b;
        $a = $t;
    }
    return abs($a);
}

$app->post('/games', function (Request $request, Response $response) use ($db) {
    $input = json_decode($request->getBody()->getContents(), true);
    $playerName = $input['player'] ?? 'Игрок';
    $num1 = rand(1, 100);
    $num2 = rand(1, 100);
    $correctGcd = gcd($num1, $num2);

    $stmt = $db->prepare("INSERT INTO games (player_name, num1, num2, correct_gcd) VALUES (?, ?, ?, ?)");
    $stmt->execute([$playerName, $num1, $num2, $correctGcd]);

    $gameId = $db->lastInsertId();
    $response->getBody()->write(json_encode([
        'id' => $gameId,
        'num1' => $num1,
        'num2' => $num2
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/step/{id}', function (Request $request, Response $response, array $args) use ($db) {
    $gameId = $args['id'];
    $input = json_decode($request->getBody()->getContents(), true);
    $playerGcd = (int) $input['gcd'];


    $stmt = $db->prepare("SELECT correct_gcd FROM games WHERE id = ?");
    $stmt->execute([$gameId]);
    $correctGcd = $stmt->fetchColumn();


    $result = ($playerGcd == $correctGcd) ? 'Верно' : 'Неверно';


    $stmt = $db->prepare("UPDATE games SET player_gcd = ?, result = ? WHERE id = ?");
    $stmt->execute([$playerGcd, $result, $gameId]);

    $response->getBody()->write(json_encode([
        'correct_gcd' => $correctGcd,
        'player_gcd' => $playerGcd,
        'result' => $result
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/games', function (Request $request, Response $response) use ($db) {
    $stmt = $db->query("SELECT * FROM games ORDER BY created_at DESC");
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($games));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
