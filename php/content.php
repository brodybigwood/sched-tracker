<?php

session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {

    header("Location: login.php");
    exit; 
}

$page = $_GET['page'];

$allowedTypes = ['schedule', 'chat', 'settings'];


if (!in_array($page, $allowedTypes)) {
    echo 'error';
    exit();
}

$username = $_SESSION['username'];
$isAdmin = false;

try {
    $db = new SQLite3('../weeks.db');
    $stmt = $db->prepare("SELECT is_admin FROM Employees WHERE name = :username LIMIT 1");
    $stmt->bindValue(':username',$username, SQLITE3_TEXT);

    $result = $stmt->execute();

    if($result) {
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if($row) {
            if($row['is_admin'] ==1) {
                $isAdmin = true;
            }
        }
        $result->finalize();
    }
    $db->close();
} catch (Exception $e) {
    error_log("SQLITE3 ERROR: " . $e->getMessage());
}

if($page == 'schedule') {
    readfile('schedule.html');
} elseif($page == 'chat') {
    readfile('chat.html');
} elseif (!$isAdmin) {
    readfile('development.html');
} elseif($page == 'settings') {
    readfile('settings.html');
} else {
    echo json_encode('error');
}

?>