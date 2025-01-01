<?php
// Include the database connection file
$host = 'localhost';
$dbname = 'placement_management';
$username = 'root';
$password = '';

try {
    global $pdo; // Ensure $pdo is globally accessible
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
session_start();

// Ensure the user is logged in as Placement Cell
if ($_SESSION['role'] != 'placement_cell') {
    die("Unauthorized access.");
}

$success = ""; // Initialize success message

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = $_POST['company_name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $eligibility_criteria = $_POST['eligibility_criteria'];

    $stmt = $pdo->prepare("INSERT INTO placement_drives (company_name, date, time, eligibility_criteria) VALUES (?, ?, ?, ?)");
    $stmt->execute([$company_name, $date, $time, $eligibility_criteria]);

    // Set a success message in the session and redirect
    $_SESSION['success'] = "Placement drive added successfully!";
    header("Location: add_drive.php");
    exit(); // Ensure no further code is executed
}

// Display success message if available
if (!empty($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']); // Clear the success message
}

// Fetch all placement drives
$stmt = $pdo->query("SELECT * FROM placement_drives ORDER BY date DESC");
$drives = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Placement Drive</title>
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
            max-width: 900px;
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

        h1, h2 {
            text-align: center;
            color: #ecf0f1;
            margin-bottom: 20px; /* Reduced margin-bottom */
            font-size: 2.5em;
            font-weight: bold;
            transition: color 0.3s ease;
            line-height: 1.2; /* Reduced line-height */
        }

        h1:hover, h2:hover {
            color: #1abc9c;
        }

        .section {
            margin-bottom: 30px; /* Reduced margin-bottom */
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

        input {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #7f8c8d;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background-color: #2c3e50;
            color: #ecf0f1;
        }

        input:focus {
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

        .success {
            color: #27ae60;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .drives {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .drive {
            background-color: #2c3e50;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            flex: 1 1 calc(33.333% - 20px);
            min-width: 250px;
        }

        .drive:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .drive h3 {
            color: #1abc9c;
            margin-bottom: 10px;
        }

        .drive p {
            margin-bottom: 10px;
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
            float: right;
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #34495e;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .profile-dropdown-content a {
            color: #ecf0f1;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .profile-dropdown-content a:hover {
            background-color: #1abc9c;
        }

        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }

        .profile-icon {
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.7); /* Added white shadow */
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="profile-dropdown">
                <span class="profile-icon">&#128100;</span>
                <div class="profile-dropdown-content">
                    <a href="../logout.php">Logout</a>
                </div>
            </div>
            <h1>Placement Management</h1>
        </header>

        <!-- Add Placement Drive Section -->
        <div class="section" id="add-drive">
            <h2>Add Placement Drive</h2>
            <?php if (!empty($success)): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" required>

                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>

                <label for="time">Time</label>
                <input type="time" id="time" name="time" required>

                <label for="eligibility_criteria">Eligibility Criteria (CGPA)</label>
                <input type="number" step="0.1" id="eligibility_criteria" name="eligibility_criteria" required>

                <button type="submit">Add Drive</button>
            </form>
        </div>

        <!-- Ongoing Placement Drives Section -->
        <div class="section" id="ongoing-drives">
            <h2>Ongoing Placement Drives</h2>
            <div class="drives">
                <?php if (!empty($drives)): ?>
                    <?php foreach ($drives as $drive): ?>
                        <div class="drive">
                            <h3><?php echo htmlspecialchars($drive['company_name']); ?></h3>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($drive['DATE']); ?></p>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars($drive['TIME']); ?></p>
                            <p><strong>Eligibility Criteria:</strong> <?php echo htmlspecialchars($drive['eligibility_criteria']); ?> CGPA</p>
                            <form method="GET" action="view_applicants.php">
                                <input type="hidden" name="drive_id" value="<?php echo $drive['drive_id']; ?>">
                                <button type="submit">View Applicants</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No placement drives added yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
