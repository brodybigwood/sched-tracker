<?php

$infile = '../currentWeek.json';

$currentWeekJson = file_get_contents($infile);
$employees = json_decode($currentWeekJson, true); // Decode JSON into an associative array

if (is_array($employees)) {
    foreach ($employees as $employee => $employeeData) {
        generateUser($employeeData);
    }
} else {
    echo "<p>Error: Could not decode JSON or the data is not an array.</p>";
}


function generateUser($employeeData) {
    echo "<div class='employee'>";
    generateName($employeeData);
    generateShifts($employeeData);
    echo "</div>";
}

function generateName($employeeData) {

    $employeeName = htmlspecialchars($employeeData['name']);
    echo "<h3>Name: " . $employeeName . "</h3>";

}

function generateShifts($employeeData) {
    $employeeShifts = $employeeData['shifts'];

    echo "<h4>Shifts:</h4><ul>";
    echo "<div class='shiftList'>";
    foreach ($employeeShifts as $day => $workday) {
        if (empty($workday)) {
            echo "<h3>No Shift</h3>";
            continue;
        }
        echo "<div class='workday'>";
        foreach ($workday as $shift => $shiftData) {

            generateShift($shiftData);

        }
        echo "</div>";
    }

    echo "</div>";
}

function generateShift($shiftData) {
    echo "<div class='shift'>";
    $hours = $shiftData['Hours'];

    $start = htmlspecialchars($hours['start']);
    $end = htmlspecialchars($hours['end']);

    echo "<h3>Hours: " . $start . " to " . $end . "</h3>";

    $role = htmlspecialchars($shiftData['Role']);
    echo "<h3>Role: " . $role . "</h3>";
    echo "</div>";
}

?>