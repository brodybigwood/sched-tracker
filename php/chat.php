<?php

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: Adjust in production to specific domains
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {

    header("Location: login.php");
    exit; 
}


// Handle OPTIONS requests (preflight for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}


$cohereApiKey = getenv('COHERE_API_KEY');
if (!$cohereApiKey) {
    echo json_encode(['error' => 'Cohere API key not configured.']);
    http_response_code(502);
    exit;
}

$dbPath = '../weeks.db'; // Path to your SQLite database
// Ensure this directory and file are writable by the web server user
// E.g., chmod 775 /var/www/html/your_database.sqlite (or stricter)

// --- Database Interaction Functions ---
function getDbConnection($dbPath) {
    try {
        $db = new SQLite3($dbPath);
        $db->exec('PRAGMA foreign_keys = ON;');
        // Ensure table exists (create if not)
        return $db;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

function getUserChatData($db, $userName) {
    $stmt = $db->prepare("SELECT chat_memory, last_chat_conversation FROM Employees WHERE name = :name LIMIT 1");
    $stmt->bindValue(':name', $userName, SQLITE3_TEXT);
    $result = $stmt->execute();
    $userData = $result ? $result->fetchArray(SQLITE3_ASSOC) : null;
    $result->finalize();
    $stmt->close();

    return $userData;
}

function updateChatData($db, $username, $chatMemory, $lastChatConversation) {
    $stmt = $db->prepare("UPDATE Employees SET chat_memory = :memory, last_chat_conversation = :last_convo WHERE name = :username");
    $stmt->bindValue(':memory', $chatMemory, SQLITE3_TEXT);
    $stmt->bindValue(':last_convo', $lastChatConversation, SQLITE3_TEXT);
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->execute();
    $stmt->close();
}


// --- Cohere API Call Function ---
function callCohereAPI($messages, $cohereApiKey) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.cohere.ai/v2/chat'); // Cohere Chat endpoint
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $cohereApiKey,
        'X-Cohere-Api-Version: 2024-05-10', // Recommended API version
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'command-a-03-2025', // Or 'command-r', 'command-a-03-2025' if available/suitable
        'messages' => $messages,
        'temperature' => 0.2, // Adjust as needed
    ]));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("cURL Error: " . $error);
        return ['error' => 'Failed to connect to Cohere API: ' . $error, 'http_code' => 500];
    }
    if ($httpCode != 200) {
        error_log("Cohere API Error (HTTP $httpCode): " . $response);
        return ['error' => 'Cohere API returned an error (HTTP ' . $httpCode . '): ' . $response, 'http_code' => $httpCode];
    }
      // BOM (Byte Order Mark) issue, needs removing to decode

    return $response;
}


// Your preamble (can be loaded from a file or database)
// This should be your full set of instructions for the AI
function getPreamble($userName) {
    $preambleFilePath = '../preamble.txt';
    $preambleContent = file_get_contents($preambleFilePath);
    
    // Replace placeholder if you have one in the file, otherwise omit this line
    $finalPreamble = str_replace('{USER_NAME_PLACEHOLDER}', $userName, $preambleContent);
    return $finalPreamble;
}

// --- Main API Logic ---



$db = getDbConnection($dbPath);
if (!$db) {
    echo json_encode(['error' => 'Database connection failed.']);
    http_response_code(500);
    exit;
}

session_start();

$input = json_decode(file_get_contents('php://input'), true);

$userName = $_SESSION['username'];
$userMessage = $input['message'] ?? 'test';


if (!$userName || !$userMessage) {
    echo json_encode(['error' => 'Invalid input: userName or message missing.']);
    http_response_code(400);
    exit;
}


$userData = getUserChatData($db, $userName);
$chatMemory = $userData['chat_memory']; // This is your text summary/context for AI
$lastChatConversation = $userData['last_chat_conversation']; // This is the last raw exchange

// Start building messages for Cohere
$messages = [];

// 1. Add preamble (as a system message if Cohere supports it, or user)
// Cohere Command-A/R models typically handle a 'system' role well.
$messages[] = ['role' => 'system', 'content' => getPreamble($userName)];

// 2. Add chat_memory if it exists (as 'system' or 'user' to provide context)
if ($chatMemory) {
    $messages[] = ['role' => 'system', 'content' => "Previous summarized conversation context: " . $chatMemory];
}

// 3. Add last_chat_conversation if it exists (as 'user' followed by 'assistant' to simulate turns)
// This simulates the previous interaction for the AI to understand continuity
if ($lastChatConversation) {
    // Attempt to parse "User: ... || AI: ..." format
    $parts = explode(' || AI: ', $lastChatConversation, 2);
    if (count($parts) === 2) {
        $userPrevMsg = str_replace('User: ', '', $parts[0]);
        $aiPrevMsg = $parts[1];
        $messages[] = ['role' => 'user', 'content' => $userPrevMsg];
        $messages[] = ['role' => 'assistant', 'content' => $aiPrevMsg];
    } else {
        // Fallback for unexpected format, just add as a system message
        $messages[] = ['role' => 'system', 'content' => "Last conversation: " . $lastChatConversation];
    }
}

// 4. Add the current user's message
$messages[] = ['role' => 'user', 'content' => $userMessage];

// --- First Call to Cohere ---
$cohereResponse = callCohereAPI($messages, $cohereApiKey);

if (isset($cohereResponse['error'])) {
    echo json_encode($cohereResponse);
    http_response_code($cohereResponse['http_code'] ?? 500);
    $db->close();
    exit;
}

$cohereResponseArray = json_decode($cohereResponse, true);

$aiMessageText = '';
$aiMessageText = $cohereResponseArray['message']['content'][0]['text'];

$jsonString = ''; // Initialize an empty string for the extracted JSON

// 1. Find the first occurrence of '```json'
$startTag = '```json';
$startIndex = strpos($aiMessageText, $startTag);

if ($startIndex !== false) {
    // 2. Adjust startIndex to be after '```json'
    $startIndex += strlen($startTag);

    // 3. Find the first occurrence of '```' after the start tag
    $endTag = '```';
    $endIndex = strpos($aiMessageText, $endTag, $startIndex);

    if ($endIndex !== false) {
        // 4. Extract the substring between the tags
        $jsonString = substr($aiMessageText, $startIndex, $endIndex - $startIndex);
        $jsonString = trim($jsonString); // Remove leading/trailing whitespace
    } else {
        // Handle case where '```json' is found but no closing '```'
        error_log("WARNING: Found '```json' but no closing '```' in AI message.");
        // You might want to assign the rest of the string or handle as an error
        $jsonString = trim(substr($aiMessageText, $startIndex));
    }
} else {
    // Handle case where '```json' is not found
    error_log("INFO: No '```json' block found in AI message. Attempting to decode full string directly.");
    $jsonString = trim($aiMessageText); // Assume the whole string might be JSON
}

function day_toInt($dayString) {
    $timestamp = strtotime($dayString);
    if ($timestamp === false) return $dayString;

    $targetDay = (int)date('w', $timestamp); // 0=Sunday, 1=Monday, ..., 6=Saturday
    $dayIndex = $targetDay === 0 ? 6 : $targetDay - 1; // Make Monday=0, Sunday=6

    $now = strtotime('today');
    $weekStart = strtotime('monday this week', $now);

    $daysDifference = floor(($timestamp - $weekStart) / 86400); // 86400 = seconds in a day
    $weekOffset = floor($daysDifference / 7);

    return ['week' => $weekOffset, 'day' => $dayIndex];
}



function inform($decoded) {
    $respone = [];
    foreach ($decoded as $request) {
        $day = day_toInt($request);
        $response[] = $day;
    }
    return json_encode($respone);
}

$decoded = json_decode($jsonString, true);

if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    $response = inform($decoded);
    echo json_encode(['response' => $response]);

} else {
    echo json_encode(['response' => $aiMessageText]);
}


$db->close();



?>