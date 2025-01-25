<?php
header('Content-Type: application/json');

$output = [];

// Call the bluetooth agent
exec("tarsier_bluetooth_agent.exe --list", $output);

$response = json_decode(implode("\n", $output), true);

if ($response && isset($response['status'])) {
    if ($response['status'] === 'success') {
        echo json_encode($response['devices']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => $response['message']]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected response from Bluetooth agent.']);
}
?>
