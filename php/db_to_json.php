<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$databaseFile = '../weeks.db';



$queryType = $_GET['queryType'] ?? null;

$allowedTypes = ['Employees', 'Weeks', 'Shifts', 'Availabilities', 'Positions'];


if (!in_array($queryType, $allowedTypes)) {
    echo 'error';
    exit(); // Stop script execution if $queryType is not in $allowedTypes
}

try {
    $db = new SQLite3($databaseFile);

    $weekDate = $_GET['week'] ?? null;

    $week = explode('-', $weekDate);

    $year = intval($week[0]);
    $month = intval($week[1])+1; 
    $day = intval($week[2]);


    $stmt = $db->prepare("SELECT week_id FROM Weeks WHERE year = :year AND month = :month AND day = :day");

    if (!$stmt) {
        echo "Error preparing statement: " . $db->lastErrorMsg() . "<br>";
        die();
    }

    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':month', $month, SQLITE3_INTEGER);
    $stmt->bindValue(':day', $day, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if (!$result) {
        echo "Error executing statement: " . $db->lastErrorMsg() . "<br>";
        die();
    }


    $weekResult = $result->fetchArray(SQLITE3_ASSOC);

    if (empty($weekResult)) {
        echo json_encode(null);
        exit();
    } 
    $weekId = $weekResult['week_id'];



 
    $listItems = [];

    $results = $db->query("SELECT * FROM " . $queryType);

    if($queryType == 'Employees') {
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {

            $shiftMatches = $db->query("SELECT * FROM Shifts WHERE week_id = " . $weekId . " AND employee_id = " . $row['employee_id']);
            $shiftsForEmployee = [];
            while ($shiftRow = $shiftMatches->fetchArray(SQLITE3_ASSOC)) {
                $shiftsForEmployee[] = $shiftRow;
            }
            $row['shifts'] = $shiftsForEmployee; // Add 'shifts' as a key to the $row array
            $listItems[] = $row;
        }
    } else {
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $listItems[] = $row;
        }
    }

    $results->finalize();
    
    header('Content-Type: application/json');
    echo json_encode($listItems);
    exit();

    $db->close();

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
?>