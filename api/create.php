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

$content = $data['content']; // Set data content
if (isset($data['construct_esc']) && $data['construct_esc'] == true) {
    // If check construct ESC/POS command
    // then overwrite the content to add compatibility
    $builder = new EscPosBuilder();
    $commands = $builder->initializePrinter();
    $commands .= $builder->setAlignment('center');
    $commands .= $builder->printText("This is a test print.");
    $commands .= $builder->setAlignment('left');
    $commands .= $builder->printText($data['content']);
    $commands .= $builder->cutPaper();

    $content = $commands;
}

try {
    // Create a temporary file
    $tempDir = sys_get_temp_dir();
    $tempFileName = uniqid('bluetooth_', true) . '.txt';
    $tempFilePath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;

    // Write the content to the file
    file_put_contents($tempFilePath, $content);

    echo json_encode(['tempFilePath' => $tempFilePath]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create temp file', 'details' => $e->getMessage()]);
}

class EscPosBuilder
{
    // Function to initialize the printer
    function initializePrinter()
    {
        return "\x1B\x40"; // ESC @
    }

    // Function to set text alignment
    function setAlignment($alignment)
    {
        switch ($alignment) {
            case 'left':
                return "\x1B\x61\x00"; // ESC a 0
            case 'center':
                return "\x1B\x61\x01"; // ESC a 1
            case 'right':
                return "\x1B\x61\x02"; // ESC a 2
            default:
                return "\x1B\x61\x00"; // Default to left alignment
        }
    }

    // Function to print text
    function printText($text)
    {
        return $text . "\x0A"; // Append newline character
    }

    // Function to cut the paper
    function cutPaper()
    {
        return "\x1D\x56\x00"; // GS V 0
    }
}
