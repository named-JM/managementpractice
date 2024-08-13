<?php
include "../db_connection.php";

if (isset($_GET['pos_id'])) {
    $pos_id = $_GET['pos_id'];

    // Fetch the benefits associated with the given position
    $query = $conn->prepare("
        SELECT b.ben_name 
        FROM position_benefits pb
        JOIN benefits b ON pb.ben_id = b.ben_id
        WHERE pb.pos_ref = ?
    ");
    $query->bind_param("i", $pos_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        echo "<ul class='pl-5 text-left list-disc'>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . $row['ben_name'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "No benefits assigned to this position.";
    }

    $query->close();
}

$conn->close();
?>
