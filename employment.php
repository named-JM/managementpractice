<?php
include "db_connection.php";

session_start();

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contractual_name = $_POST['contractual_name'] ?? '';
    $compensation = $_POST['compensation'] ?? '';
    $terms = $_POST['terms'] ?? '';
    $duration = $_POST['duration'] ?? '';

    if (!empty($contractual_name) && !empty($compensation) && !empty($terms) && !empty($duration)) {
        $stmt = $conn->prepare("INSERT INTO employment (contractual_name, employ_compensation, employ_terms, employ_duration, employ_status) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("ssss", $contractual_name, $compensation, $terms, $duration);

        if ($stmt->execute()) {
            $successMessage = "New record created successfully.";
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "All fields are required.";
    }
}

//FETCHING ALL RECORD FROM THE EPMOLYMENT DATATABLE THAST HAS BEEN ADDEDED
$result = $conn->query("SELECT * FROM employment");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment Contract Form</title>

    <!-- datatable style cdn -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.min.css">
    <!-- font awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <!-- EMPLOYMENT FORM STARTS HERE!!!! -->
    <h1>Employment Contract Form</h1>
    <!-- PAGES NAVIGATION LINK-->
    <a href="benefits_management/benefits.php">Benefits Management</a>
    <a href="position_management/position.php">Position Management</a>
    <a href="employee_management/add_employee.php">Employee Management</a>
    <br><br>
    <form action="employment.php" method="post">
        <!-- CONTRACT NAME -->
        <label for="contractual_name">Contractual Name:</label>
        <input type="text" id="contractual_name" name="contractual_name">
        <br><br>
        <!-- COMPENSATION FORM DROPDOWN -->
        <label for="compensation">Compensation Type:</label>
        <select id="compensation" name="compensation">
            <option value="">Select Compensation</option>
            <option value="contractual">Contractual</option>
            <option value="fixed rate">Fixed Rate</option>
            <option value="per day">Per Day</option>
            <option value="project-based">Project-Based</option>
            <option value="commission-based">Commission Based</option>
            <option value="piece worker">Piece Worker</option>
        </select>
        <br><br>
        <!-- TERMS DROPDOWN TIME/DAY -->
        <label for="terms">Terms:</label>
        <select id="terms" name="terms">
            <option value="">Select Term</option>
            <option value="time">Time</option>
            <option value="day">Day</option>
        </select>
        <br><br>
        <!-- DURATION INPUT NUMBER -->
        <label id="durationLabel" for="duration">Duration:</label>
        <input type="number" id="duration" name="duration">
        <br><br>
        <!-- SUBMIT -->
        <input type="submit" value="Submit">
    </form>
    <!-- JUST A PROMPT FOR SUCCESS ADDING -->
    <?php if ($successMessage): ?>
        <p><?php echo $successMessage; ?></p>
    <?php elseif ($errorMessage): ?>
        <p><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <!-- HERES THE DISPLAY TABLE!!! -->
    <h2>Table</h2>
    <table border="1" id="contract_table">
        <thead>
            <tr>
                <th>Contractual Name</th>
                <th>Compensation Type</th>
                <th>Terms</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            <!-- KINUKUHA SA DATABASE NA NA-ADD -->
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['contractual_name']; ?></td>
                    <td><?php echo $row['employ_compensation']; ?></td>
                    <td><?php echo $row['employ_terms']; ?></td>
                    <td>
                    <!-- CONDITION FOR TIME = HOURS DISPLAY AND DAY = DAYS DISPLAY -->
                    <?php
                    if($row['employ_terms'] == 'Time'){
                        echo $row['employ_duration'] . " hrs";
                    } elseif($row['employ_terms'] == 'Day') {
                        echo $row['employ_duration'] . " days";
                    } else {
                        echo "No data ";
                    }
                    ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
    <!-- DataTables Initialization Script -->
    <script>
        $(document).ready( function () {
            $('#contract_table').DataTable();
        });
    </script>
</body>
</html>


<?php
$conn->close();
?>
