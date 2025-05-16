<?php
$databaseFile = '../weeks.db';



$queryType = $_GET['queryType'] ?? null;

$allowedTypes = ['Employees', 'Weeks', 'Shifts', 'Availabilities', 'Positions'];

if (!in_array($queryType, $allowedTypes)) {
    exit(); // Stop script execution if $queryType is not in $allowedTypes
}

try {
    $db = new SQLite3($databaseFile);

    // Get the requested week ID (e.g., from a GET request)
    $weekId = $_GET['weekId'] ?? null;

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