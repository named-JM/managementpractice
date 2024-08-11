<?php

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_hrprimo_hris";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if(!$conn){
        die ("Connection Failed" . mysqli_connect_error());

    }
    else {
        echo "";
    }

?>