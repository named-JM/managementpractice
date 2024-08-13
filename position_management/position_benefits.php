<?php
include "../db_connection.php";

if (isset($_GET['pos_id'])) {
    $pos_id = $_GET['pos_id'];

    // Fetch the position details
    $positionQuery = $conn->prepare("SELECT pos_name FROM position WHERE pos_id = ?");
    $positionQuery->bind_param("i", $pos_id);
    $positionQuery->execute();
    $positionResult = $positionQuery->get_result();
    $position = $positionResult->fetch_assoc();

    // Fetch the benefits associated with this position
    $benefitsQuery = $conn->prepare("
        SELECT b.ben_id, b.ben_name
        FROM position_benefits pb
        JOIN benefits b ON pb.ben_id = b.ben_id
        WHERE pb.pos_ref = ?
    ");
    $benefitsQuery->bind_param("i", $pos_id);
    $benefitsQuery->execute();
    $benefitsResult = $benefitsQuery->get_result();

    // Fetch all benefits
    $allBenefitsQuery = $conn->prepare("SELECT ben_id, ben_name FROM benefits");
    $allBenefitsQuery->execute();
    $allBenefitsResult = $allBenefitsQuery->get_result();

    // Handle adding benefits to the position
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $selected_benefits = $_POST['benefit'] ?? [];

        // Insert each selected benefit into the position_benefits table
        $insertStmt = $conn->prepare("INSERT INTO position_benefits (pos_ref, ben_id) VALUES (?, ?)");
        foreach ($selected_benefits as $ben_id) {
            if (!empty($ben_id)) {
                $insertStmt->bind_param("ii", $pos_id, $ben_id);
                $insertStmt->execute();
            }
        }
        $insertStmt->close();

        // Redirect to the same page to refresh the data
        header("Location: position_benefits.php?pos_id=$pos_id");
        exit;
    }

    // Close the statements
    $positionQuery->close();
    $benefitsQuery->close();
    $allBenefitsQuery->close();
} else {
    // Redirect back to position.php if pos_id is not set
    header("Location: position.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Position Benefits</title>
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
        $('#pos_benefits_table').DataTable();


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
        title: 'Add Position Benefits Form',
        html: `
        <form id="posBenefitsForm" action="position_benefits.php?pos_id=<?php echo htmlspecialchars($pos_id); ?>" method="POST">
        <select name="benefit[]" id="benefitDropdown" class="block w-full p-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
    <option value="">Select Benefits</option>
    <?php
    // Reset the result pointer for all benefits
    $allBenefitsResult->data_seek(0);
    while($benefit = $allBenefitsResult->fetch_assoc()) {
        $isAssigned = false;
        $benefitsResult->data_seek(0); // Reset the result pointer for assigned benefits
        while($assignedBenefit = $benefitsResult->fetch_assoc()) {
            if($assignedBenefit['ben_id'] == $benefit['ben_id']) {
                $isAssigned = true;
                break;
            }
        }
        $style = $isAssigned ? 'color: red;' : '';
        $disabled = $isAssigned ? 'disabled' : '';
        echo '<option value="'.htmlspecialchars($benefit['ben_id']).'" style="'.$style.'" '.$disabled.'>'.htmlspecialchars($benefit['ben_name']).'</option>';
    }
    ?>
</select>

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
            document.getElementById('posBenefitsForm').submit();
        }
    });
});
    });


</script>

</head>

<body class="p-20 m-2 bg-gray-100">

    <h2 class="mb-4 text-2xl font-bold">Benefits for Position: <?php echo htmlspecialchars($position['pos_name']); ?></h2>
    
    <button type="button" id="openFormBtn"
        class="relative px-5 py-3 overflow-hidden text-white bg-indigo-500 rounded shadow min-w-max hover:bg-opacity-90">
        Add Position
    </button>

    <table border="1" id="pos_benefits_table" class="w-full bg-white rounded-lg shadow-lg display">
        <thead>
            <tr>
                <th>Benefit Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($benefitsResult->num_rows > 0): ?>
                <?php $benefitsResult->data_seek(0); // Reset the result pointer ?>
                <?php while($benefit = $benefitsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($benefit['ben_name']); ?></td>
                        <td>
                            <form action="remove_benefits.php" method="POST" onsubmit="return confirm('Are you sure you want to remove this benefit?');">
                                <input type="hidden" name="pos_id" value="<?php echo htmlspecialchars($pos_id); ?>">
                                <input type="hidden" name="ben_id" value="<?php echo htmlspecialchars($benefit['ben_id']); ?>">
                                <button type="submit" class="inline-flex items-center px-4 py-1 text-red-500 transition hover:text-red-600"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No benefits found for this position.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- <a href="position.php" class="inline-block mt-4 text-blue-500 hover:underline">Back to Positions</a> -->

</body>
</html>

<?php
$conn->close();
?>
