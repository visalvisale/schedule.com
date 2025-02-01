<?php
// Include the database connection file
include('../db.php');
session_start();

// Check if the user is logged in and is a 'user'
if (!isset($_SESSION['usernames']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

// Include your database connection
include('../db.php');

// Fetch user-specific data (e.g., image) from the database
$username = $_SESSION['usernames'];
$sql = "SELECT image FROM admins WHERE usernames = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $imagePath = $user['image'];
} else {
    $imagePath = 'default_image.jpg';  // Set a default image if not found
}
// Handle form submission for adding new records
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'insert') {
    $date = $_POST['date'];
    $time = $_POST['time'];
    $name = $_POST['name_teacher'];  // Corrected: use 'name_teacher' from the form
    $subject = $_POST['subject'];
    $class = $_POST['class'];

    // Prepare SQL to check for duplicate data based on date, time, and class
    $sql_check = "SELECT * FROM sechdules WHERE date = ? AND time = ? AND class = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("sss", $date, $time, $class);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    // Check if the query returns rows (i.e., duplicate data found)
    if ($result->num_rows > 0) {
        echo "<script>alert('មានបញ្ហា: (ដូចគ្នា ថ្ងៃ, ម៉ោង, និង ថ្នាក់)')</script>.";
    } else {
        // Prepare SQL to check if the name already exists with the same date and time but different class
        $sql_check_name = "SELECT * FROM sechdules WHERE date = ? AND time = ? AND name = ?";
        $stmt_check_name = $conn->prepare($sql_check_name);
        $stmt_check_name->bind_param("sss", $date, $time, $name);
        $stmt_check_name->execute();
        $result_name = $stmt_check_name->get_result();

        // If the name exists with a different class, show an error
        if ($result_name->num_rows > 0) {
            echo "Error: The name already exists with a different class.";
        } else {
            // Insert new data into the database
            $sql_insert = "INSERT INTO sechdules (date, time, name, subject, class) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssss", $date, $time, $name, $subject, $class);

            if ($stmt_insert->execute()) {
                echo "<script>alert('ទិន្នន័យបញ្ជូលបានជោគជ័យ')</script>";
            } else {
                echo "Error: " . $stmt_insert->error;
            }
        }
    }
}

// Handle record editing
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $sql_edit = "SELECT * FROM sechdules WHERE id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("i", $id);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    $edit_record = $result_edit->fetch_assoc();
}

// Handle record update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = $_POST['id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $name = $_POST['name_teacher'];  // Corrected: use 'name_teacher' from the form
    $subject = $_POST['subject'];
    $class = $_POST['class'];

    $sql_update = "UPDATE sechdules SET date = ?, time = ?, name = ?, subject = ?, class = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssi", $date, $time, $name, $subject, $class, $id);

    if ($stmt_update->execute()) {
        echo "<script>alert('ការកែប្រែទិន្នន័យបានជោគជ័យ')</script>";
    } else {
        echo "Error: " . $stmt_update->error;
    }
}

// Handle record deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql_delete = "DELETE FROM sechdules WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);

    if ($stmt_delete->execute()) {
        echo "<script style='font-size: 24px; color:green'>alert('ទិន្នន័យលុបបានជោគជ័យ')</script>";
    } else {
        echo "Error: " . $stmt_delete->error;
    }
}

// Retrieve all records for display
$sql_select = "SELECT * FROM sechdules";
$result_select = $conn->query($sql_select);

// Retrieve teacher names for the form
$query = "SELECT id, name FROM imformation"; // Adjust table and column names as needed
$result = mysqli_query($conn, $query);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title style="font-size: 24px; color:green">បញ្ជូលកាលវិភាគ</title>
    <link rel="stylesheet" href="admin.css">
</head>
<style>
    .schedule {
        margin-top: -39%;
        text-align: center;
        background-color: greenyellow;
        width: 300px;
        margin-left: 40%;
    }
    table {
        width: 55%;
        text-align: center;
        margin-top: 5%;
        margin-left: 30%;
    }
    table th {
        background-color: greenyellow;
    }
    select {
       width: 180px;
}
script {
    font-size: 24px;
    color: green;
}
</style>
<body>
<div class="navbar">
        <img src="../uploads/Screenshot 2025-01-18 105358.png" alt="Logo">
        <div class="link">
            <ul>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
    <hr>
    <div class="pic">
        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="admin Image">
        <br>
        <span>Admin, <?php echo htmlspecialchars($_SESSION['usernames']); ?></span>
    </div>
    <hr style="margin-left: -30px; width: 280px">
    <div class="links">
        <ul><br>
            <li><a href="#">មហាវិទ្យាល័យ</a></li><br>
            <li><a href="schedule_admin.php">កាលវិភាគ</a></li><br>
            <li><a href="display_teacher.php">គ្រូបង្រៀន</a></li><br>
            <li><a href="#">បន្ថែម Admin</a></li><br>
            <li><a href="#">ជូនដំណឹង</a></li><br>
            <li><a href="#">មតិនិស្សិត</a></li>
        </ul>
    </div>

    <div class="schedule">
    <h2>Form បញ្ជូលទិន្នន័យ</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?= isset($edit_record) ? 'update' : 'insert' ?>">
        <?php if (isset($edit_record)) { ?>
            <input type="hidden" name="id" value="<?= $edit_record['id'] ?>">
        <?php } ?>
        <label for="date" >ថ្ងៃ:</label>
        <select id="date" name="date" required>
            <option value="ច័ន្ទ" <?= isset($edit_record) && $edit_record['date'] == 'ច័ន្ទ' ? 'selected' : '' ?>>ច័ន្ទ</option>
            <option value="អង្គារិ៍" <?= isset($edit_record) && $edit_record['date'] == 'អង្គារិ៍' ? 'selected' : '' ?>>អង្គារិ៍</option>
            <option value="ពុធ" <?= isset($edit_record) && $edit_record['date'] == 'ពុធ' ? 'selected' : '' ?>>ពុធ</option>
            <option value="ព្រហស្សត្តិ៍" <?= isset($edit_record) && $edit_record['date'] == 'ព្រហស្សត្តិ៍' ? 'selected' : '' ?>>ព្រហស្សត្តិ៍</option>
            <option value="សុក្រ" <?= isset($edit_record) && $edit_record['date'] == 'សុក្រ' ? 'selected' : '' ?>>សុក្រ</option>
        </select><br><br>

        <label for="time">ម៉ោង:</label>
        <select id="time" name="time" required>
            <option value="7:30 - 9:00" <?= isset($edit_record) && $edit_record['time'] == '7:30 - 9:00' ? 'selected' : '' ?>>7:30 - 9:00</option>
            <option value="10:30 - 12:00" <?= isset($edit_record) && $edit_record['time'] == '10:30 - 12:00' ? 'selected' : '' ?>>10:30 - 12:00</option>
            <option value="7:30 - 9:00 - 10:30" <?= isset($edit_record) && $edit_record['time'] == '7:30 - 9:00 - 10:30' ? 'selected' : '' ?>>7:30 - 9:00 - 10:30</option>
            <option value="9:00 - 10:30 - 12:00" <?= isset($edit_record) && $edit_record['time'] == '9:00 - 10:30 - 12:00' ? 'selected' : '' ?>>9:00 - 10:30 - 12:00</option>
        </select><br><br>

        <label for="name_teacher">ឈ្មោះគ្រូ:</label>
        <select name="name_teacher" id="name_teacher" required>
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Display each teacher name as an option
                    echo '<option value="' . $row['name'] . '"' . (isset($edit_record) && $edit_record['name'] == $row['name'] ? ' selected' : '') . '>' . $row['name'] . '</option>';
                }
            } else {
                echo '<option value="">No teachers available</option>';
            }
            ?>
        </select><br><br>

        <label for="subject">មុខវិជ្ជា:</label>
        <input type="text" id="subject" name="subject" value="<?= isset($edit_record) ? $edit_record['subject'] : '' ?>" required><br><br>

        <label for="class">ថ្នាក់:</label>
        <input type="text" id="class" name="class" value="<?= isset($edit_record) ? $edit_record['class'] : '' ?>" required><br><br>

        <input type="submit" value="<?= isset($edit_record) ? 'កែប្រែ' : 'បញ្ជូលទិន្នន័យ' ?>">
    </form>
</div>
    
    <table border="1">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Name</th>
                <th>Subject</th>
                <th>Class</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_select->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['date'] ?></td>
                    <td><?= $row['time'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['subject'] ?></td>
                    <td><?= $row['class'] ?></td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>"><button>Edit</button></a> |
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this record?')"><button>Delete</button></a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
