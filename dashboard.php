<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

// Redirect based on role
$role = $_SESSION['role'];

if ($role === 'student') {
    header("Location: student/view_drives.php");
    exit;
} elseif ($role === 'placement_cell') {
    header("Location: placement_cell/add_drive.php");
    exit;
} elseif ($role === 'hod') {
    header("Location: hod/view_participation.php");
    exit;
} else {
    echo "Invalid role detected. Please try again.";
}
?>
