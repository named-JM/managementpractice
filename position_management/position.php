<?php
include "../db_connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $pos_name = $_POST['pos_name'];
    $pos_employment = $_POST['pos_employment'];
    $pos_salary = $_POST['pos_salary'];
    $pos_status = "active";

    // Insert data into the position table
    $stmt = $conn->prepare("INSERT INTO position (pos_name, pos_employment, pos_salary, pos_status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sids", $pos_name, $pos_employment, $pos_salary, $pos_status);
    if ($stmt->execute()) {
        $pos_id = $stmt->insert_id;

        // Retrieve selected benefits
        $benefits = $_POST['pos_ref'] ?? [];

        // Insert each benefit into the position_benefits table
        $stmt = $conn->prepare("INSERT INTO position_benefits (pos_ref, ben_id) VALUES (?, ?)");
        foreach ($benefits as $ben_id) {
            if (!empty($ben_id)) { // Skip empty values
                $stmt->bind_param("ii", $pos_id, $ben_id);
                $stmt->execute();
            }
        }
        $stmt->close();

        header("Location: position.php?success=1");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// FETCHING DATA IN EMPLOYMENT TABLE, BENEFITS TABLE, POSITION TABLE
$contracts = $conn->query("SELECT employ_id, contractual_name FROM employment");
$benefits = $conn->query("SELECT ben_id, ben_name FROM benefits");
$result = $conn->query("SELECT * FROM position");


// WHERE BENEFITS DROPWDOWN IT WILL DISPLAY THE NAME OF IT AND VALUE OF ITS ID
$benefitOptions = "";
if ($benefits->num_rows > 0) {
    while ($benefit = $benefits->fetch_assoc()) {
        $benefitOptions .= "<option value='" . $benefit['ben_id'] . "'>" . $benefit['ben_name'] . "</option>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Position Management</title>
    <style>
        .benefit-group {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <!-- POSITION FORM MANAGEMENT!!-->
    <h1>POSITION MANAGEMENT</h1>
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <p>Position and benefits added successfully.</p>
    <?php endif; ?>
    <form action="position.php" method="post">
        <a href="../employment.php">back to employment</a>
        <h3>Add New Position</h3>
        
        <!-- POSITION NAME!! -->
        <label for="pos_name">Position name</label>
        <input type="text" id="pos_name" name="pos_name" required>
        <br><br>

        <!-- CONTRACTS / KUNG ANON YUNG MGA COMPENSATION!!! -->
        <!-- dropdown pos_employment which is yung contract doon sa employment kukunin -->
        <label for="pos_employment">Contracts</label>
        <select name="pos_employment" id="pos_employment" required>
            <option value="">Select Contract</option>
            <?php
            if ($contracts->num_rows > 0) {
                while ($contract = $contracts->fetch_assoc()) {
                    echo "<option value='" . $contract['employ_id'] ."'>" . $contract['contractual_name']. "</option>";
                }
            } else {
                echo "<option value=''>No contracts available</option>";
            }
            ?>
        </select>
        <br><br>

        <!-- SALARY -->
        <label for="pos_salary">Salary</label>
        <input type="number" id="pos_salary" name="pos_salary" required>
        <br><br>

        <!-- BENEFITS -->
        <!-- dropdown then may plus icon for adding another dropdown and yung ma value
        nandoon sa benefits management -->
        <label for="add_benefits">Benefits:</label>
        <div id="benefits-container">
            <div class="benefit-group">
                <select name="pos_ref[]" class="benefit-select">
                    <option value="">Select Benefit</option>
                    <?php echo $benefitOptions; ?>
                </select>
                <button type="button" onclick="addBenefit()">+</button>
            </div>
        </div>
        <br><br>

        <input type="submit" value="Submit">
    </form>

    <h2>Table</h2>
    <table border="1">
        <tr>
            <th>Position Name</th>
            <th>Contract</th>
            <th>Salary</th>
        </tr>
        <!-- KINUKUHA SA DATABASE NA NA-ADD -->
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['pos_name']; ?></td>
                <td><?php echo $row['pos_employment']; ?></td>
                <td><?php echo $row['pos_salary']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>


    <!-- SCRIPT JUST FOR REMOVING DROPFDOWN, IT WILL GONE POOF -->
    <script>
        const benefitOptions = `<?php echo $benefitOptions; ?>`;

        function addBenefit() {
            const benefitsContainer = document.getElementById('benefits-container');
            const newBenefitGroup = document.createElement('div');
            newBenefitGroup.className = 'benefit-group';
            newBenefitGroup.innerHTML = `
                <select name="pos_ref[]" class="benefit-select">
                    <option value="">Select Benefit</option>
                    ${benefitOptions}
                </select>
                <button type="button" onclick="removeBenefit(this)">-</button>
            `;
            benefitsContainer.appendChild(newBenefitGroup);
        }

        function removeBenefit(button) {
            button.parentElement.remove();
        }
    </script>

</body>
</html>

<?php
$conn->close();
?>
