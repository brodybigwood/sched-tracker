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

if($page == 'schedule') {
    readfile('schedule.html');
} elseif($page == 'chat') {
    readfile('chat.html');
} elseif($page == 'settings') {
    readfile('settings.html');
} else {
    echo json_encode('error');
}

?>