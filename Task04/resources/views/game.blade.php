<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Игра на НОД</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; }
        button, input { margin: 5px; padding: 8px; }
        #output { margin-top: 20px; border: 1px solid #ccc; padding: 10px; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Игра на НОД</h1>
    <div>
        <input type="text" id="playerName" placeholder="Имя игрока">
        <button onclick="startGame()">Начать новую игру</button>
    </div>
    <div>
        <input type="number" id="gameId" placeholder="ID игры">
        <input type="number" id="playerGcd" placeholder="Ваш НОД">
        <button onclick="makeStep()">Сделать ход</button>
    </div>
    <div>
        <button onclick="getGames()">Показать все игры</button>
    </div>
    <div id="output">Вывод информации...</div>

    <script>
        const output = document.getElementById('output');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function startGame() {
            const player = document.getElementById('playerName').value || 'Игрок';
            const res = await fetch('/games', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ player })
            });
            const data = await res.json();
            output.innerHTML = `<p>Новая игра ID: ${data.id}. Числа: ${data.num1} и ${data.num2}</p>`;
        }

        async function makeStep() {
            const gameId = document.getElementById('gameId').value;
            const playerGcd = document.getElementById('playerGcd').value;
            if (!gameId || !playerGcd) {
                output.innerHTML = '<p>Введите ID игры и свой ответ!</p>';
                return;
            }
            const res = await fetch(`/step/${gameId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ gcd: playerGcd })
            });
            const data = await res.json();
            output.innerHTML = `
                <p>Ваш ответ: ${data.player_gcd}</p>
                <p>Правильный НОД: ${data.correct_gcd}</p>
                <p>Результат: <strong>${data.result}</strong></p>
            `;
        }

        async function getGames() {
            const res = await fetch('/games');
            const games = await res.json();
            let html = '<h3>Список игр:</h3>';
            games.forEach(game => {
                html += `ID: ${game.id}, Игрок: ${game.player_name}, Числа: ${game.num1} и ${game.num2}, НОД: ${game.correct_gcd}, Ваш ответ: ${game.player_gcd || '-'}, Результат: ${game.result || '-'}<br>`;
            });
            output.innerHTML = html;
        }
    </script>
</body>
</html>
