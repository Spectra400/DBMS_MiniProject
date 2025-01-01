<?php
    
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $department = $_POST['department'] ?? null;
    $cgpa = $_POST['cgpa'] ?? null;
    $usn = $_POST['usn'] ?? null;

    if ($role === 'student') {
        $stmt = $pdo->prepare("INSERT INTO students (name, email, password, department, cgpa, usn) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $department, $cgpa, $usn]);
    } elseif ($role === 'hod') {
        $stmt = $pdo->prepare("INSERT INTO hods (name, email, password, department) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $department]);
    } elseif ($role === 'placement_cell') {
        $stmt = $pdo->prepare("INSERT INTO placement_cells (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $password]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Management System - Register</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #ecf0f1;
        }

        .container {
            background-color: #2c3e50;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h1 {
            color: #1abc9c;
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }

        p {
            color: #bdc3c7;
            margin-bottom: 1.5rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        label {
            color: #ecf0f1;
            font-weight: bold;
            text-align: left;
        }

        input, select {
            padding: 0.8rem;
            border: 1px solid #34495e;
            border-radius: 5px;
            font-size: 1rem;
            background-color: #34495e;
            color: #ecf0f1;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #1abc9c;
        }

        button {
            background: linear-gradient(135deg, #1abc9c, #16a085);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #16a085, #1abc9c);
        }

        a {
            color: #1abc9c;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        #departmentField, #cgpaField, #usnField {
            display: none;
        }

        .success-message {
            background-color: #27ae60;
            color: #ecf0f1;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register for Placement Management System</h1>
        <form method="post" action="register.php">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            
            <label for="role">Role:</label>
            <select id="role" name="role" onchange="toggleFields()" required>
                <option value="" disabled selected>Select your role</option>
                <option value="student">Student</option>
                <option value="hod">HOD</option>
                <option value="placement_cell">Placement Cell</option>
            </select>
            
            <div id="departmentField">
                <label for="department">Department:</label>
                <select id="department" name="department" onchange="toggleCustomField(this)">
                    <option value="" disabled selected>Select your Department</option>
                    <option value="CSE">CSE</option>
                    <option value="ISE">ISE</option>
                    <option value="CSE:AIML">CSE:AIML</option>
                    <option value="ECE">ECE</option>
                    <option value="MECH&RA">MECH&RA</option>
                    <option value="other">Other</option>
                </select>
                <input type="text" id="customDepartment" placeholder="Enter new department" style="display: none;" oninput="validateDepartmentInput(this)">
                <input type="hidden" id="finalDepartment" name="department">
            </div>
            
            <div id="cgpaField">
                <label for="cgpa">CGPA:</label>
                <input type="number" step="0.1" id="cgpa" name="cgpa" placeholder="Enter your CGPA">
            </div>
            
            <div id="usnField">
                <label for="usn">USN:</label>
                <input type="text" id="usn" name="usn" placeholder="Enter your USN">
            </div>
            
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="index.php">Login here</a>.</p>
        <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                echo "<p class='success-message'>Registration successful! You can now <a href='index.php'>login</a>.</p>";
            }
        ?>
    </div>

    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            document.getElementById('cgpaField').style.display = role === 'student' ? 'block' : 'none';
            document.getElementById('departmentField').style.display = role === 'student' || role === 'hod' ? 'block' : 'none';
            document.getElementById('usnField').style.display = role === 'student' ? 'block' : 'none';
        }

        function toggleCustomField(select) {
            const customDepartment = document.getElementById('customDepartment');
            const finalDepartment = document.getElementById('finalDepartment');

            if (select.value === 'other') {
                customDepartment.style.display = 'block';
                customDepartment.oninput = () => {
                    finalDepartment.value = customDepartment.value; // Update hidden field
                };
            } else {
                customDepartment.style.display = 'none';
                customDepartment.value = ''; // Clear custom input
                finalDepartment.value = select.value; // Set hidden field to dropdown value
            }

            // Initialize hidden field with dropdown value
            finalDepartment.value = select.value;
        }

        function validateDepartmentInput(input) {
            input.value = input.value.toUpperCase(); // Convert input to uppercase
        }
    </script>
</body>
</html>