<?php
session_start();
include('../db.php');  // Ensure this connects to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['usernames'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if the passwords match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    // Check if the email already exists
    $sql = "SELECT * FROM admins WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "The email is already registered. Please use a different email.";
        exit();
    }

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);

        // Validate image extension (only allow certain file types)
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($imageExtension), $allowedExtensions)) {
            // Generate a unique file name
            $newImageName = uniqid() . '.' . $imageExtension;
            $uploadDir = 'image/'; // Define your upload directory
            $uploadPath = $uploadDir . $newImageName;

            // Move the uploaded file to the desired location
            if (move_uploaded_file($imageTmpName, $uploadPath)) {
                $imagePath = $uploadPath;
            }
        } else {
            echo "Invalid image type. Only jpg, jpeg, png, and gif are allowed.";
            exit();
        }
    }

    // Hash password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $sql = "INSERT INTO admins (usernames, email, password, image) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $imagePath);

    if ($stmt->execute()) {
        echo "Registration successful!";
        header("Location: login_admin.php"); // Redirect to the login page
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="login.css">
  <style>
    /* Add some custom styles for the password visibility toggle */
    .password-container {
      position: relative;
    }

    .password-container input {
      padding-right: 30px;  /* Space for the icon */
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
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
            <li><a href="register.php">Register</a></li>
            <li><a href="login_admin.php">Login</a></li>
        </ul>
    </div>
  </div>
  
  <div class="all">
    <h2>Register</h2>
    <form method="POST" action="register_admin.php" enctype="multipart/form-data">
      <input type="text" name="usernames" placeholder="Username" required><br><br>
      <input type="email" name="email" placeholder="Email" required><br><br>

      <!-- Password Field -->
      <div class="password-container">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <span class="toggle-password" id="togglePassword" onclick="togglePasswordVisibility()">👁️</span>
      </div><br><br>

      <!-- Confirm Password Field -->
      <div class="password-container">
        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" required>
        <span class="toggle-password" id="toggleConfirmPassword" onclick="toggleConfirmPasswordVisibility()">👁️</span>
      </div><br><br>

      <input type="file" name="image" accept="image/*"><br><br> <!-- Image upload field -->
      <button type="submit">Register</button>
    </form>
  </div>

  <script>
    // Function to toggle password visibility for the 'password' field
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

    // Function to toggle password visibility for the 'confirm_password' field
    function toggleConfirmPasswordVisibility() {
      var confirmPasswordField = document.getElementById("confirmPassword");
      var icon = document.getElementById("toggleConfirmPassword");
      if (confirmPasswordField.type === "password") {
        confirmPasswordField.type = "text";
        icon.textContent = "🙈"; // Change icon to indicate the password is visible
      } else {
        confirmPasswordField.type = "password";
        icon.textContent = "👁️"; // Change icon back to indicate the password is hidden
      }
    }
  </script>
</body>
</html>
