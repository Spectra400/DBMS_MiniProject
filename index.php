<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Management System</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            display: flex;
            max-width: 900px;
            margin: 0 auto;
            background-color: #34495e;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .left {
            flex: 1;
            background-color: #2c3e50;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .left h1 {
            color: #1abc9c;
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .left p {
            color: #ecf0f1;
            font-size: 1.2em;
        }

        .right {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h2 {
            color: #ecf0f1;
            margin-bottom: 20px;
            font-size: 2em;
            text-align: center;
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

        a {
            color: #1abc9c;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #16a085;
        }

        .error {
            color: #ff4444;
            font-size: 0.9rem;
            margin-top: 0.2rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <h1>Placement Management System</h1>
            <p>Please log in to access the system.</p>
        </div>
        <div class="right">
            <h2>Login</h2>
            <form method="post" action="login.php">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="student">Student</option>
                    <option value="placement_cell">Placement Cell</option>
                    <option value="hod">HOD</option>
                </select>
                
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </div>
</body>
</html>