<?php
include "../db_connection.php";

$ben_id = $_GET['ben_id'] ?? '';

// CHECKING FOR OVERLAPPING IN THE DATA TABLE RANGES!!!
function isRangeOverlap($conn, $ben_id, $start, $end) {
    $stmt = $conn->prepare("SELECT * FROM benefits_lists WHERE ben_id = ? AND ((ben_list_range_s <= ? AND ben_list_range_e >= ?) OR (ben_list_range_s <= ? AND ben_list_range_e >= ?))");
    $stmt->bind_param("idddd", $ben_id, $end, $end, $start, $start);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->num_rows > 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ben_list_range_s = $_POST['ben_list_range_s'] ?? '';
    $ben_list_range_e = $_POST['ben_list_range_e'] ?? '';
    $ben_employee_amount = $_POST['ben_employee_amount'] ?? '';
    $ben_employer_amount = $_POST['ben_employer_amount'] ?? '';

    // JUST A DISPLAYING
    echo "<br>ben_id: $ben_id<br>";
    echo "ben_list_range_s: $ben_list_range_s<br>";
    echo "ben_list_range_e: $ben_list_range_e<br>";
    echo "ben_employee_amount: $ben_employee_amount<br>";
    echo "ben_employer_amount: $ben_employer_amount<br>";

    if (!empty($ben_list_range_s) && !empty($ben_list_range_e) && !empty($ben_employee_amount) && !empty($ben_employer_amount)) {
        if (isRangeOverlap($conn, $ben_id, $ben_list_range_s, $ben_list_range_e)) {
            echo "Error: The range overlaps with an existing range.";
        } else {
            //CHECKING IF BEN ID EXIST IN THE TABLE
            $stmt_check = $conn->prepare("SELECT ben_id FROM benefits WHERE ben_id = ?");
            $stmt_check->bind_param("i", $ben_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            // INSERTING
            if ($result_check->num_rows == 0) {
                echo "Error: ben_id does not exist in benefits table.";
            } else {
                // INSERTING/ADDING BENEFITS LIST
                $stmt = $conn->prepare("INSERT INTO benefits_lists (ben_id, ben_list_range_s, ben_list_range_e, ben_employee_amount, ben_employer_amount) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("idddd", $ben_id, $ben_list_range_s, $ben_list_range_e, $ben_employee_amount, $ben_employer_amount);

                if ($stmt->execute()) {
                    echo "New benefits list entry added successfully.";
                } else {
                    echo "Error: " . $stmt->error;
                }
                $stmt->close();
            }
            $stmt_check->close();
        }
    } else {
        echo "All fields are required.";
    }
}

// Fetch benefits list for specific ben_id
$sql = "SELECT * FROM benefits_lists WHERE ben_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ben_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benefits List</title>
    <!-- TAILWING CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- tialiwind css file -->
    <link href="./output.css" rel="stylesheet">
    <!-- datatable style cdn -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
    <!-- font awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- SWEETALERT2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- As an old-school alternative, you can initialize the plugin by referencing the necessary files: -->
    <script src="sweetalert2.all.min.js"></script>
    <!-- Or with the stylesheet separately if desired: -->
    <script src="sweetalert2.min.js"></script>
    <link rel="stylesheet" href="sweetalert2.min.css">
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
    <!-- DataTables Initialization Script -->
    <script>
        $(document).ready( function () {
            $('#benefits_table').DataTable();
        });
    </script>
</head>
<body class="bg-gray-100 p-20 m-2">
    <br><br>
    <!-- NAVIGATION PAGE LINKS -->
    <a href="benefits.php">Back to benefits</a>
    <h1>Benefits List</h1>

    <form action="benefits_list.php?ben_id=<?php echo $ben_id; ?>" method="post">
        <label for="ben_list_range_s">Range Start:</label>
        <input type="number" id="ben_list_range_s" name="ben_list_range_s" step="0.01">
        <br><br>
        <label for="ben_list_range_e">Range End:</label>
        <input type="number" id="ben_list_range_e" name="ben_list_range_e" step="0.01">
        <br><br>
        <label for="ben_employee_amount">Employee Amount:</label>
        <input type="number" id="ben_employee_amount" name="ben_employee_amount" step="0.01">
        <br><br>
        <label for="ben_employer_amount">Employer Amount:</label>
        <input type="number" id="ben_employer_amount" name="ben_employer_amount" step="0.01">
        <br><br>
        <input type="submit" value="Submit">
    </form>

    

    <h2>Benefits List for ben_id: <?php echo $ben_id; ?></h2>
    <table border="1" id="benefits_table" class="display w-full bg-white rounded-lg shadow-lg">
        <thead>
        <tr>
            <th>Range Start</th>
            <th>Range End</th>
            <th>Employee Amount</th>
            <th>Employer Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
    
            <td><?php echo $row['ben_list_range_s']; ?></td>
            <td><?php echo $row['ben_list_range_e']; ?></td>
            <td><?php echo $row['ben_employee_amount']; ?></td>
            <td><?php echo $row['ben_employer_amount']; ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
