<?php
include "../db_connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $pos_name = $_POST['pos_name'];
    $pos_employment = $_POST['pos_employment'];
    $pos_salary = $_POST['pos_salary'];
    $pos_status = "active";

    // Generate the unique POS date identifier
    $milliseconds = round(microtime(true) * 1000);
    $formattedDate = date('YmdHis') . substr($milliseconds, -3); // Get current date and time in 'YYYYMMDDHHMMSS' format and add milliseconds
    $pos_date = "POS-" . $formattedDate;

    // Insert data into the position table
    $stmt = $conn->prepare("INSERT INTO position (pos_name, pos_employment, pos_salary, pos_status, pos_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sidss", $pos_name, $pos_employment, $pos_salary, $pos_status, $pos_date);
    if ($stmt->execute()) {
        $pos_id = $stmt->insert_id;

        // Retrieve selected benefits
        $benefits = $_POST['pos_ref'] ?? [];

        // Insert each benefit into the position_benefits table
        $stmt = $conn->prepare("INSERT INTO position_benefits (pos_ref, ben_id) VALUES (?, ?)");
        foreach ($benefits as $ben_id) {
            if (!empty($ben_id)) { 
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
$contracts = $conn->query("SELECT employ_id, contractual_name, employ_compensation, employ_terms FROM employment");
$benefits = $conn->query("SELECT ben_id, ben_name FROM benefits");
$result = $conn->query("SELECT * FROM position");

// Modify the query to join the employment table and fetch employ_compensation
$result = $conn->query("
    SELECT p.*, e.employ_compensation
    FROM position p
    JOIN employment e ON p.pos_employment = e.employ_id
");

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
   
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS CDN -->
    <script src="https://cdn.datatables.net/2.1.3/js/dataTables.min.js"></script>
    <!-- DataTables Initialization Script -->
    <script>
    $(document).ready(function () {
        $('#position_table').DataTable();


        // BUTTON CLICK EFFECT
        function rippleEffect(event) {
                const btn = event.currentTarget;

                const circle = document.createElement("span");
                const diameter = Math.max(btn.clientWidth, btn.clientHeight);
                const radius = diameter / 2;

                circle.style.width = circle.style.height = `${diameter}px`;
                circle.style.left = `${event.clientX - (btn.offsetLeft + radius)}px`;
                circle.style.top = `${event.clientY - (btn.offsetTop + radius)}px`;
                circle.classList.add("ripple");

                const ripple = btn.getElementsByClassName("ripple")[0];

                if (ripple) {
                    ripple.remove();
                }
                btn.appendChild(circle);
            }
            const btn = document.getElementById("openFormBtn");
            btn.addEventListener("click", rippleEffect);
            
        // MODAL FORM SWWEEETALERT!!!!

        $('#openFormBtn').on('click', function () {
    Swal.fire({
        title: 'Benefits Form',
        html: `
            <!-- POSITION FORM STARTS HERE!!!! -->
            <form id="positionForm" action="position.php" method="post" class="space-y-4 text-left">
                
                <!-- POSITION NAME!! -->
                <div>
                    <label for="pos_name" class="block text-sm font-medium text-gray-700">Position Name</label>
                    <input type="text" id="pos_name" name="pos_name" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <!-- CONTRACTS / KUNG ANON YUNG MGA COMPENSATION!!! -->
                <div>
                    <label for="pos_employment" class="block text-sm font-medium text-gray-700">Contracts</label>
                    <select name="pos_employment" id="pos_employment" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">Select Contract</option>
                        <?php
                        if ($contracts->num_rows > 0) {
                            while ($contract = $contracts->fetch_assoc()) {
                                echo "<option value='" . $contract['employ_id'] ."'>" .  $contract['employ_compensation']. " - ". $contract['employ_terms']. "</option>";
                            }
                        } else {
                            echo "<option value=''>No contracts available</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- SALARY -->
                <div>
                    <label for="pos_salary" class="block text-sm font-medium text-gray-700">Salary</label>
                    <input type="number" id="pos_salary" name="pos_salary" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <!-- BENEFITS -->
                <div id="benefits-container">
                    <label for="add_benefits" class="block text-sm font-medium text-gray-700">Benefits</label>
                    <div class="flex items-center space-x-2 benefit-group">
                        <select name="pos_ref[]" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 benefit-select">
                            <option value="">Select Benefit</option>
                            <?php echo $benefitOptions; ?>
                        </select>
                        <button type="button" class="px-3 py-1 text-white bg-blue-500 rounded-md hover:bg-blue-600" onclick="addBenefit()">+</button>
                    </div>
                </div>

            </form>
        `,
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: 'Submit',
        width: '500px',
        customClass: {
            popup: 'swal-wide', // Additional custom class if needed
        },
        preConfirm: () => {
            // Validate form data here if needed
            document.getElementById('positionForm').submit();
        }
    });
});

});

</script>
    
    <style>
        .benefit-group {
            margin-bottom: 10px;
        }
        .swal2-popup {
            width: 500px !important;
            padding: 20px !important;
            text-align: left;
        }
        .swal2-content {
            text-align: left !important;
        }
        .swal2-input {
            width: 100% !important;
            margin-bottom: 15px;
        }
        .swal2-select {
            width: 100% !important;
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        
        span.ripple {
        position: absolute;
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 600ms linear;
        background-color: rgba(255, 255, 255, 0.7);
        }
        @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
        }
    </style>
</head>

<body class="p-20 m-2 bg-gray-100">

    <!-- POSITION FORM MANAGEMENT!!-->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <p>Position and benefits added successfully.</p>
    <?php endif; ?>

    <button type="button" id="openFormBtn"
        class="relative px-5 py-3 overflow-hidden text-white bg-indigo-500 rounded shadow min-w-max hover:bg-opacity-90">
        Add Position
    </button>


    <table border="1" id="position_table" class="w-full bg-white rounded-lg shadow-lg display">
        <thead>
        <tr>
            <th>Position Name</th>
            <th>Contract</th>
            <th>Salary</th>
            <th>Action</th>
            
        </tr>
        </thead>
        <!-- KINUKUHA SA DATABASE NA NA-ADD -->
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['pos_name']; ?></td>
                <td><?php echo $row['employ_compensation']; ?></td> <!-- Display compensation instead of employ_id -->
                <!-- <td>php echo $row['pos_employment']; </td> -->
                <td><?php echo $row['pos_salary']; ?></td>
            
                <td>
                <!-- link to the position_benefits kung nasaan yung position name yun yung kukunin to see anong beneftis na nakapa loob doon-->
                <a href="position_benefits.php?pos_id=<?php echo $row['pos_id']; ?>" class='inline-flex items-center px-4 py-1 text-white bg-blue-500 rounded hover:bg-blue-700'>View Benefits</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>


    <!-- SCRIPT JUST FOR REMOVING DROPFDOWN, IT WILL GONE POOF -->
    <script>
        const benefitOptions = `<?php echo $benefitOptions; ?>`;

        function addBenefit() {
        const benefitsContainer = document.getElementById('benefits-container');
        const newBenefitGroup = document.createElement('div');
        newBenefitGroup.className = 'benefit-group flex items-center space-x-2 mt-2';
        newBenefitGroup.innerHTML = `
        <select name="pos_ref[]" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 benefit-select">
            <option value="">Select Benefit</option>
            ${benefitOptions}
        </select>
        <button type="button" class="px-3 py-1 text-white bg-red-500 rounded-md hover:bg-red-600" onclick="removeBenefit(this)">-</button>
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
