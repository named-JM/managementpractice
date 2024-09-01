<!-- DATABASE CONNECTION FILE TO MAKE THE PAGES INCLUDE THIS FILE -->
<?php

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_hrprimo_hris";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if(!$conn){
        //if the database connection failed will display error.
        die ("Connection Failed" . mysqli_connect_error());

    }
    else {
        // if the database is successfully connected display none.
        echo "";
    }

?>