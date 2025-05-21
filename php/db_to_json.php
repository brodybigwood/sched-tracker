<?php

session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {

    header("Location: login.php");
    exit; 
}

header('Content-Type: application/json');

$databaseFile = '../weeks.db';



$queryType = $_GET['queryType'] ?? null;

$allowedTypes = ['Employees', 'Weeks', 'Shifts', 'Availabilities', 'Positions'];


if (!in_array($queryType, $allowedTypes)) {
    echo 'error';
    exit();
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
    } else if ($queryType == 'Shifts') {
        $shiftMatches = $db->query("SELECT * FROM Shifts WHERE week_id = " . $weekId);
        while ($shiftRow = $shiftMatches->fetchArray(SQLITE3_ASSOC)) {
            $employee_id = $shiftRow['employee_id'];

            $get_employee = $db->query('SELECT * FROM EMPLOYEES WHERE employee_id = ' . $employee_id);
            $employee = $get_employee->fetchArray(SQLITE3_ASSOC);
            $employee_name = $employee['name'];

            $shift = array(
                'start_time' => $shiftRow['start_time'],
                'end_time' => $shiftRow['end_time'],
                'day_of_week' => $shiftRow['day_of_week'],
                'assigned_position' => $shiftRow['assigned_position'],
                'employeeName' => $employee_name
            );
            $listItems[] = $shift;
        }

    } else {
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $listItems[] = $row;
        }
    }

    $results->finalize();
    

    echo json_encode($listItems);
    $db->close();
    exit();



} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
?>