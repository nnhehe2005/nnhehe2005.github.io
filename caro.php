
<?php
// file: game.php

session_start();
$game_file = 'game_state.json';
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents($game_file), true);

if ($action == 'reset') {
    $data = ['board' => array_fill(0, 15, array_fill(0, 15, '')), 'turn' => 'X'];
    file_put_contents($game_file, json_encode($data));
    echo json_encode(['status' => 'reset']);
    exit;
}

if ($action == 'move') {
    $x = (int) $_GET['x'];
    $y = (int) $_GET['y'];
    
    if ($data['board'][$x][$y] == '') {
        $data['board'][$x][$y] = $data['turn'];
        $data['turn'] = $data['turn'] == 'X' ? 'O' : 'X';
        file_put_contents($game_file, json_encode($data));
    }
    echo json_encode($data);
    exit;
}

if ($action == 'state') {
    echo json_encode($data);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caro Multiplayer</title>
    <style>
        table { border-collapse: collapse; }
        td {
            width: 40px; height: 40px;
            text-align: center; font-size: 24px;
            border: 1px solid black; cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Caro Multiplayer</h1>
    <button onclick="resetGame()">Reset Game</button>
    <table id="board"></table>

    <script>
        let boardSize = 15;
        function renderBoard(data) {
            let board = document.getElementById('board');
            board.innerHTML = '';
            for (let i = 0; i < boardSize; i++) {
                let row = document.createElement('tr');
                for (let j = 0; j < boardSize; j++) {
                    let cell = document.createElement('td');
                    cell.textContent = data.board[i][j];
                    cell.onclick = () => makeMove(i, j);
                    row.appendChild(cell);
                }
                board.appendChild(row);
            }
        }

        function fetchGameState() {
            fetch('game.php?action=state')
                .then(response => response.json())
                .then(data => renderBoard(data));
        }

        function makeMove(x, y) {
            fetch(`game.php?action=move&x=${x}&y=${y}`)
                .then(response => response.json())
                .then(data => renderBoard(data));
        }

        function resetGame() {
            fetch('game.php?action=reset')
                .then(response => response.json())
                .then(() => fetchGameState());
        }

        setInterval(fetchGameState, 1000);
        fetchGameState();
    </script>
</body>
</html>
