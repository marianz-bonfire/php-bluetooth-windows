<?php
header('Content-Type: application/json');

// Read JSON input
$request = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
if (!isset($request['printer']) || !isset($request['path'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters: printer and path.']);
    exit;
}

$printer = escapeshellarg($request['printer']);
$path = escapeshellarg($request['path']);

$output = [];

// Call the bluetooth agent
$command = "tarsier_bluetooth_agent.exe --print=$printer --path=$path";
exec($command, $output, $return_var);

// Parse the output from the bluetooth agent
$response = json_decode(implode("\n", $output), true);

if ($response && isset($response['status'])) {
    if ($response['status'] === 'success') {
        echo json_encode(['message' => $response['message']]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $response['message']]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected response from Bluetooth agent.', 'response' => json_encode($response), 'command' => $command]);
}
?>
