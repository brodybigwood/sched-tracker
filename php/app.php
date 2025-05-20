<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Schedule</title>
    <link rel="stylesheet" href="style.css">
    <script defer src="script.js"></script>
    <link rel="manifest" href="manifest.json">
</head>
<body>

    <div id="top-bar" class="fixed-bar">
        <div id="view">
            <a id="title">Schedule View</a>
            <a id="viewType"></a>
        </div>
        <button id="dropdown" class="navigator"></button>
    </div>

    <div id="sub-top" class="fixed-bar">
        <button id="prev-week" class="week-nav" onclick="week(-1)">Previous Week</button>
        <a id="current-week-display"></a>
        <button id="next-week" class="week-nav" onclick="week(1)">Next Week</button>
    </div>

    <ul class="dropdown-list">
        <li>Next Week...</li>
        <li>Previous Week...</li>
        <li>Employees</li>
        <li>Days</li>
        <li>Positions</li>
    </ul>

    <div id="table">

    </div>

    <div id="bottom-bar" class="fixed-bar">
        <button class="navigator" id="scheduleBtn"></button>
    </div>
</body>
</html>