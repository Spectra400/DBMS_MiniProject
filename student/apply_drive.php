<?php
// Include the database connection file
$host = 'localhost';
$dbname = 'placement_management';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

session_start();

// Ensure the user is logged in as a student
if ($_SESSION['role'] != 'student') {
    die("Unauthorized access.");
}

// Variables to hold success or error messages
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $drive_id = $_POST['drive_id'];

    // Validate form data
    if (empty($name) || empty($email) || empty($drive_id)) {
        $error = "All fields are required.";
    } else {
        // Fetch the student record, including CGPA and USN, from the students table
        $stmt = $pdo->prepare("SELECT student_id, cgpa, usn FROM students WHERE name = ? AND email = ?");
        $stmt->execute([$name, $email]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            $student_id = $student['student_id'];
            $cgpa = $student['cgpa']; // Fetch the student's CGPA
            $usn = $student['usn']; // Fetch the student's USN

            // Fetch the eligibility criteria for the selected placement drive
            $driveStmt = $pdo->prepare("SELECT eligibility_criteria FROM placement_drives WHERE drive_id = ?");
            $driveStmt->execute([$drive_id]);
            $drive = $driveStmt->fetch(PDO::FETCH_ASSOC);

            if ($drive) {
                $eligibility_criteria = $drive['eligibility_criteria'];

                // Compare student's CGPA with the eligibility criteria
                if ($cgpa < $eligibility_criteria) {
                    $error = "Your CGPA does not meet the eligibility criteria for this placement drive.";
                } else {
                    // Check if the student has already applied for the same drive
                    $checkStmt = $pdo->prepare("SELECT * FROM placement_participation WHERE student_id = ? AND drive_id = ?");
                    $checkStmt->execute([$student_id, $drive_id]);

                    if ($checkStmt->rowCount() > 0) {
                        $error = "You have already applied for this placement drive.";
                    } else {
                        // Insert the application into placement_participation
                        $insertStmt = $pdo->prepare(
                            "INSERT INTO placement_participation (student_id, name, email, cgpa, usn, drive_id) VALUES (?, ?, ?, ?, ?, ?)"
                        );
                        $insertStmt->execute([$student_id, $name, $email, $cgpa, $usn, $drive_id]);
                        $success = "You have successfully applied for the placement drive!";
                    }
                }
            } else {
                $error = "Invalid placement drive selected.";
            }
        } else {
            $error = "No matching student record found. Please ensure your details are correct.";
        }
    }
}

// Fetch available placement drives
$drivesStmt = $pdo->query("SELECT * FROM placement_drives ORDER BY date ASC");
$drives = $drivesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Placement Drive</title>
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Arial', sans-serif;
        line-height: 1.6;
        background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
        padding: 20px;
        color: #ecf0f1;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
        background-color: #34495e;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }

    h1 {
        text-align: center;
        color: #ecf0f1;
        margin-bottom: 30px;
        font-size: 2.5em;
        font-weight: bold;
        transition: color 0.3s ease;
    }

    h1:hover {
        color: #1abc9c;
    }

    .success, .error {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 1em;
        font-weight: bold;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .success {
        background-color: #27ae60;
        color: #ecf0f1;
    }

    .error {
        background-color: #e74c3c;
        color: #ecf0f1;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    label {
        margin-bottom: 8px;
        color: #ecf0f1;
        font-weight: bold;
        font-size: 1.1em;
    }

    input, select {
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #7f8c8d;
        border-radius: 8px;
        font-size: 1em;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        background-color: #2c3e50;
        color: #ecf0f1;
    }

    input:focus, select:focus {
        outline: none;
        border-color: #1abc9c;
        box-shadow: 0 0 8px rgba(26, 188, 156, 0.2);
    }

    button {
        background-color: #1abc9c;
        color: white;
        padding: 15px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.1em;
        font-weight: bold;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    button:hover {
        background-color: #16a085;
        transform: translateY(-5px);
    }

    .invalid {
        border-color: #e74c3c;
    }

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.9em;
        margin-top: -15px;
        margin-bottom: 15px;
        display: none;
    }
</style>
</head>
<body>
    <div class="container">
        <h1>Apply for Placement Drive</h1>
        <?php if (!empty($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="drive_id">Select Placement Drive</label>
            <select id="drive_id" name="drive_id" required>
                <option value="" disabled selected>-- Select a Drive --</option>
                <?php foreach ($drives as $drive): ?>
                    <option value="<?php echo $drive['drive_id']; ?>">
                        <?php echo htmlspecialchars($drive['company_name']) . " (Eligibility: " . $drive['eligibility_criteria'] . ", Date: " . $drive['DATE'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Apply</button>
        </form>
    </div>
</body>
</html>
