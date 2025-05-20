<?php


header("Cache-Control: max-age=0"); 
header("Cache-Control: public");      
header("Cache-Control: private");     
header("Cache-Control: no-cache"); 
header("Cache-Control: no-store");    
header("Cache-Control: must-revalidate"); 

header("Cache-Control: public, max-age=0"); 

header("Expires: " . gmdate("D, d M Y H:i:s", time() + 0) . " GMT"); 

header("Pragma: cache");
header("Pragma: no-cache");

header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");


session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // User is logged in, redirect to home.html
    header("Location: login.php");
    exit;
} else {
    // User is not logged in, redirect to login.php
    header("Location: login.php");
    exit;
}

?>