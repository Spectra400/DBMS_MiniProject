<?php
// Enable error reporting for debugging during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validate inputs
    if (empty($email) || empty($password) || empty($role)) {
        die("All fields are required.");
    }

    // Determine query based on role
    if ($role === 'student') {
        $query = "SELECT * FROM students WHERE email = ?";
    } elseif ($role === 'placement_cell') {
        $query = "SELECT * FROM placement_cells WHERE email = ?";
    } elseif ($role === 'hod') {
        $query = "SELECT * FROM hods WHERE email = ?";
    } else {
        die("Invalid role selected.");
    }

    // Execute query
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        die("No user found with this email and role.");
    }

    // Check password
    if (password_verify($password, $user['PASSWORD'])) {
        // Set session variables based on role
        if ($role === 'student') {
            $_SESSION['user_id'] = $user['student_id']; // General user_id
            $_SESSION['student_id'] = $user['student_id']; // Specific to students
            $_SESSION['role'] = $role;
            header("Location: student/view_drives.php");
        } elseif ($role === 'placement_cell') {
            $_SESSION['user_id'] = $user['placement_cell_id'];
            $_SESSION['role'] = $role;
            header("Location: placement_cell/add_drive.php");
        } elseif ($role === 'hod') {
            $_SESSION['user_id'] = $user['hod_id'];
            $_SESSION['role'] = $role;
            header("Location: hod/view_participation.php");
        }
        exit;
    } else {
        die("Invalid email or password.");
    }
} else {
    die("Invalid request method.");
}
