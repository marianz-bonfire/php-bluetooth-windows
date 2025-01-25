<?php
header('Content-Type: application/json');

// Get the request body
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

if (!isset($data['content']) || empty($data['content'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Content is required']);
    exit;
}

try {
    // Create a temporary file
    $tempDir = sys_get_temp_dir();
    $tempFileName = uniqid('bluetooth_', true) . '.txt';
    $tempFilePath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;

    // Write the content to the file
    file_put_contents($tempFilePath, $data['content']);

    echo json_encode(['tempFilePath' => $tempFilePath]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create temp file', 'details' => $e->getMessage()]);
}
?>
