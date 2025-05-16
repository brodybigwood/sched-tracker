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




echo "<!DOCTYPE html>";
echo "<html>";
    echo "<head>";
        echo "<title>Schedule</title>";
        echo "<link rel='stylesheet' href='style.css'>";
        echo "<script defer src='script.js'></script>";
        echo "<link rel='manifest' href='manifest.json'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0, viewport-fit=cover'>";
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
            "Employees",
            "Days",
            "Positions",
        ];

        echo "<ul class='dropdown-list'>";
            foreach ($views as $view => $viewString) {
                echo "<li>" . $viewString . "</li>";
            }
        echo "</ul>";

        echo "<div id='table'>";
        
            echo "</div>";

            echo "<div id='bottom-bar' class='fixed-bar'>";

                echo "<button class='navigator' id='scheduleBtn'></button>";

            echo "</div>";



    echo "</body>";
    echo "<a target='_blank' href='https://icons8.com/icon/131/search'>Search</a> icon by <a target='_blank' href='https://icons8.com'>Icons8</a>";
echo "</html>";

?>