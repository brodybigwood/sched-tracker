<?php

echo "<!DOCTYPE html>";
echo "<html>";
    echo "<head>";
        echo "<title>Schedule</title>";
        echo "<link rel='stylesheet' href='style.css'>";
        echo "<script defer src='script.js'></script>";
    echo "</head>";
    echo "<body>";





        echo "<div id='top-bar' class='fixed-bar'>";
            echo "<div id='view'>";
                echo "<a id='title'>Schedule View</a>";
                echo "<a id='viewType'></a>";
            echo "</div>";
            echo "<button id='dropdown' class='navigator'>";
            echo "</button>";

        echo "</div>";

        $views = [
            "Employee",
            "Day",
            "Position",
            "Search..."
        ];

        echo "<ul class='dropdown-list'>";
            foreach ($views as $view => $viewString) {
                echo "<li>" . $viewString . "</li>";
            }
        echo "</ul>";


        
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

        echo "<div id='bottom-bar' class='fixed-bar'>";

            echo "<button class='navigator' id='scheduleBtn'></button>";

        echo "</div>";

    echo "</body>";
    echo "<a target='_blank' href='https://icons8.com/icon/131/search'>Search</a> icon by <a target='_blank' href='https://icons8.com'>Icons8</a>";
echo "</html>";


function generateUser($employeeData) {
    echo "<div class='employee'>";
    echo "<h3>";
        generateName($employeeData);
        echo " - ";
        generateRole($employeeData);
    echo "</h3>";
    generateShifts($employeeData);
    echo "</div>";
}

function generateName($employeeData) {
    $employeeName = htmlspecialchars($employeeData['name']);
    echo $employeeName;
}

function generateRole($employeeData) {
    $employeeRole = htmlspecialchars($employeeData['role']);
    echo $employeeRole;
}

function generateShifts($employeeData) {
    $employeeShifts = $employeeData['shifts'];
    $employeeAvailability = $employeeData['availability'];
    echo "<div class='shiftList'>";
    foreach (array_values($employeeShifts) as $index => $workday) {
        echo "<div class='workday'>";



        if (empty($workday)) {

        } else {
            $prevEnd = 0;
            foreach ($workday as $shift => $shiftData) {
                $hours = $shiftData['Hours'];

                $start = htmlspecialchars($hours['start']);

                echo "<div class='empty-time' style='height: calc(100% * (" . ($start - $prevEnd) . ") / 24);'>";
                
                    $role = htmlspecialchars($shiftData['Role']);
                    echo "<h3>" . $role . "</h3>";
                echo "</div>";
                $prevEnd = generateShiftTime($shiftData);
    
            }
            echo "<div class='empty-time' style='height: calc(100% * (" . (24 - $prevEnd) . ") / 24);'></div>";
        }
        echo "</div>";
    }

    echo "</div>";
}

function generateShiftTime($shiftData) {

    $hours = $shiftData['Hours'];

    $start = htmlspecialchars($hours['start']);
    $end = htmlspecialchars($hours['end']);

    $start_str = getTimeString($start);
    $end_str = getTimeString($end);

    echo "<div class='shift' style='height: calc(100% * (" . ($end - $start) . ") / 24);'>";

        echo "<div class='shiftTime'>";

            echo "<h3>" . $start_str . " to " . $end_str . "</h3>";
        echo "</div>";

    echo "</div>";

    return $end;
}

function getTimeString(float $time24): string {
    if ($time24 < 0 || $time24 > 24) {
        return "Invalid 24-hour format";
    }

    $hours24 = floor($time24);
    $minutes = round(($time24 - $hours24) * 60);

    // Handle edge case for minutes being 60
    if ($minutes === 60) {
        $hours24++;
        $minutes = 0;
    }

    $period = "AM";
    $hours12 = $hours24;

    if ($hours24 === 0) {
        $hours12 = 12; // Midnight
    } elseif ($hours24 === 12) {
        $period = "PM"; // Noon
    } elseif ($hours24 > 12) {
        $hours12 = $hours24 - 12;
        $period = "PM";
    }

    $minuteString = str_pad($minutes, 2, '0', STR_PAD_LEFT);

    return $hours12 . ":" . $minuteString . $period;
}

?>