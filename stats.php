<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$statsFile = __DIR__ . '/stats.json';
$lockFile  = __DIR__ . '/stats.lock';

function loadStats($file) {
    if (!file_exists($file)) return [];
    $data = @json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || count($input) === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $fp = fopen($lockFile, 'c');
    if (!$fp || !flock($fp, LOCK_EX)) {
        http_response_code(500);
        echo json_encode(['error' => 'Lock failed']);
        exit;
    }

    $stats = loadStats($statsFile);

    foreach ($input as $entry) {
        $id = (string)($entry['id'] ?? '');
        if ($id === '') continue;
        $correct = !empty($entry['correct']);

        if (!isset($stats[$id])) {
            $stats[$id] = ['correct' => 0, 'total' => 0];
        }
        $stats[$id]['total']++;
        if ($correct) $stats[$id]['correct']++;
    }

    $tmp = $statsFile . '.tmp';
    file_put_contents($tmp, json_encode($stats));
    rename($tmp, $statsFile);
    flock($fp, LOCK_UN);
    fclose($fp);

    echo json_encode(['ok' => true]);

} else {
    echo json_encode(loadStats($statsFile));
}
