<?php
include "../db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ben_id = $_POST['ben_id'];
    $ben_status = $_POST['ben_status'];

    $stmt = $conn->prepare("UPDATE benefits SET ben_status = ? WHERE ben_id = ?");
    $stmt->bind_param("si", $ben_status, $ben_id);

    if ($stmt->execute()) {
        echo "Benefit status updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
