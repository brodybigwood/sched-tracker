<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Schedule</title>
    <link rel="stylesheet" href="app.css">
    <link rel="manifest" href="manifest.json">
    <script defer src="app.js"></script>
</head>
<body>
    <iframe id='currentView'></iframe>

    </div>
    <div id="bottom-bar" class="fixed-bar">
        <button class="navigator" id="chatbotBtn" onclick="setMainView('chat')"></button>
        <button class="navigator" id="scheduleBtn" onclick="setMainView('schedule')"></button></button>
        <button class="navigator" id="settingsBtn" onclick="setMainView('settings')"></button></button>
    </div>
</body>
</html>