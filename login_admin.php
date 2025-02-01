<?php
session_start();
include('../db.php');  // Make sure this connects to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['usernames'];
    $password = $_POST['password'];

    // Query to get the user data
    $sql = "SELECT * FROM admins WHERE usernames = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Start a session and store user info
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['usernames'] = $user['usernames'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role']; // Store the user's role

            // Redirect based on user role
            if ($user['role'] === 'admin') {
                // Redirect to admin dashboard
                header("Location: admin.php");
            } else {
                // Redirect to user dashboard
                header("Location: index.php");
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that usernames.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="login.css">
  <style>
    .navbar img {
            width: 75px;
            height: 65px;
            border-radius: 45%;
        }

    .password-container {
        position: relative;
    }
    .password-container input {
        padding-right: 30px; 
    }
    .password-container .toggle-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
    }
    h3 a {
      text-decoration: none;
      color: black;
    }
    h3 a:hover {
    color: greenyellow;
    transform: scale(1.1); 
    transition: transform 0.3s ease, color 0.3s ease; 
}
button {
            
            background-color: rgb(97, 174, 237);
            border: none;
            width: 100px; /* Makes button wider */
            height: 40px;
            font-size: 15px;
            color: aliceblue;
            cursor: pointer;
            border-radius: 35px; /* Adds rounded corners */
        }

        button:hover {
            background-color: red;
        }

  </style>
</head>
<body>
<div class="navbar">
    <img src="../uploads/Screenshot 2025-01-18 105358.png" alt="">
    <div class="link">
        <ul>
            <li><a href="register_admin.php">Register</a></li>
            <li><a href="login_admin.php">Login</a></li>
        </ul>
    </div>
</div>
<div class="all">
  <h2>Login</h2>
  <form method="POST" action="login_admin.php">
    <input type="text" name="usernames" placeholder="ឈ្មោះ" required><br><br>

    <!-- Password field with toggle visibility -->
    <div class="password-container">
        <input type="password" id="password" name="password" placeholder="ពាក្យសម្ងាត់" required><br><br>
        <span class="toggle-icon" id="togglePassword" onclick="togglePasswordVisibility()">👁️</span>
    </div>

    <button type="submit">បញ្ជូន</button>
  </form>
  <h3><a href="register_admin.php">Don't have account? Register</a></h3>
</div>

<script>
  // Toggle password visibility
  function togglePasswordVisibility() {
    var passwordField = document.getElementById("password");
    var icon = document.getElementById("togglePassword");
    if (passwordField.type === "password") {
      passwordField.type = "text";
      icon.textContent = "🙈"; // Change icon to indicate the password is visible
    } else {
      passwordField.type = "password";
      icon.textContent = "👁️"; // Change icon back to indicate the password is hidden
    }
  }
</script>
</body>
</html>
