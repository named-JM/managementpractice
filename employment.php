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
</head>
<style>
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
            margin-bottom: 5px;
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
<body class="bg-gray-100 p-20 m-2">
    <!-- EMPLOYMENT FORM STARTS HERE!!!! -->
    <h1 class="text-2xl font-black">Employment Contract Form</h1>
    <br><br>

    <!-- BUTTON TO OPEN THE FORM IN SWEETALERT -->
    <!-- <button id="openFormBtn" class="text-white bg-gradient-to-br from-pink-500 to-amber-500 hover:bg-gradient-to-bl bg-black focus:ring-4 focus:outline-none
    focus:ring-pink-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">Open Form</button>

    <button class="
        group
        p-2
        cursor-pointer 
        relative  
        text-sm
        font-normal 
        border-0 
        flex 
        items-center 
        justify-center
        bg-transparent
         text-red-500 
         h-auto  
         w-[200px]  
         overflow-hidden   
         transition-all
         duration-100">
        <span class="group-hover:w-full absolute left-0 h-full w-5 border-y border-l border-red-500 transition-all duration-500"></span>

        <p class="">ADD COMPENSATION</p>
        <span
            class="group-hover:w-full absolute right-0 h-full w-5  border-y border-r  border-red-500 transition-all duration-500">
        </span>
        </button> -->
<br><br>
    <button type="button" id="openFormBtn"
        class="rounded px-5 py-3 min-w-max overflow-hidden shadow relative bg-indigo-500 text-white hover:bg-opacity-90">
        Add Employee Contract
    </button>

    <script>

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
        // SWEETALERT FORM SCRIPT
        document.getElementById('openFormBtn').addEventListener('click', function() {
            Swal.fire({
            title: 'Employment Contract Form',
            html: `
            <form id="contractForm" class="text-left space-y-4">
                <!-- CONTRACT NAME -->
                <label for="contractual_name" class="text-sm font-medium">Contractual Name: <span class="text-red-500">*</span></label>
                <input type="text" id="contractual_name" name="contractual_name" class="w-full text-sm p-2 border rounded-md">
                
                <!-- COMPENSATION FORM DROPDOWN -->
                <label for="compensation" class="text-sm font-medium">Compensation Type: <span class="text-red-500">*</span></label>
                <select id="compensation" name="compensation" class="w-full text-sm p-2 border rounded-md">
                    <option value="">Select Compensation</option>
                    <option value="contractual">Contractual</option>
                    <option value="fixed rate">Fixed Rate</option>
                    <option value="per day">Per Day</option>
                    <option value="project-based">Project-Based</option>
                    <option value="commission-based">Commission Based</option>
                    <option value="piece worker">Piece Worker</option>
                </select>
                
                <!-- TERMS DROPDOWN TIME/DAY -->
                <label for="terms" class="text-sm font-medium">Terms: <span class="text-red-500">*</span></label>
                <select id="terms" name="terms" class="w-full text-sm p-2 border rounded-md">
                    <option value="">Select Term</option>
                    <option value="time">Time</option>
                    <option value="day">Day</option>
                </select>
                
                <!-- DURATION INPUT NUMBER -->
                <label id="durationLabel" for="duration" class="text-sm font-medium">Duration: <span class="text-red-500">*</span></label>
                <input type="number" id="duration" name="duration" class="w-full text-sm p-2 border rounded-md">
                <!-- ERROR MESSAGES PLACEHOLDER -->
                    <div id="errorMessages" class="text-red-500 text-sm"></div>
            </form>
            `,
            showCancelButton: true,
            cancelButtonColor: "#d33",
            confirmButtonText: 'Submit',
            width: '400px',
            customClass: {
                popup: 'swal-wide', // Additional custom class if needed
            },
            preConfirm: () => {
            const contractual_name = document.getElementById('contractual_name').value;
            const compensation = document.getElementById('compensation').value;
            const terms = document.getElementById('terms').value;
            const duration = document.getElementById('duration').value;

            // Perform validation
            if (!contractual_name || !compensation || !terms || !duration) {
                Swal.showValidationMessage('Please fill out all required fields.');
                return false;
            }

            // Send AJAX request to submit form data
            return fetch('employment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'contractual_name': contractual_name,
                    'compensation': compensation,
                    'terms': terms,
                    'duration': duration
                })
            }).then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText);
                }
                return response.text();
            }).then(result => {
                Swal.fire('Success', 'Form submitted successfully!', 'success');
                // Reload the page or fetch the updated table data if necessary
                location.reload();
            }).catch(error => {
                Swal.fire('Error', 'Form submission failed!', 'error');
            });
            }
            });
        });
    </script>
    <!-- PAGES NAVIGATION LINK-->
    <!-- <a href="benefits_management/benefits.php">Benefits Management</a>
    <a href="position_management/position.php">Position Management</a>
    <a href="employee_management/add_employee.php">Employee Management</a>
    <br><br> -->
    
    <!-- JUST A PROMPT FOR SUCCESS ADDING -->
    <?php if ($successMessage): ?>
        <p><?php echo $successMessage; ?></p>
    <?php elseif ($errorMessage): ?>
        <p><?php echo $errorMessage; ?></p>
    <?php endif; ?>


    <!-- HERES THE DISPLAY TABLE!!! -->
    <table border="1" id="contract_table" class="display w-full bg-white rounded-lg shadow-lg">
        <thead >
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
