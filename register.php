<?php

require_once "database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = trim($_POST["user_id"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $department = trim($_POST["department"]);
    $year = trim($_POST["year"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    // Basic validation
    if (
        empty($user_id) ||
        empty($name) ||
        empty($email) ||
        empty($password) ||
        empty($role)
    ) {
        $message = "Please fill all required fields.";
        $message_type = "error";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    }

    elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";
        $message_type = "error";
    }

    elseif (!in_array($role, ["student", "organizer"])) {
        $message = "Invalid role selected.";
        $message_type = "error";
    }

    else {

        // Check existing user
        $check = $conn->prepare(
            "SELECT id FROM users
             WHERE user_id = ? OR email = ?"
        );

        $check->bind_param(
            "ss",
            $user_id,
            $email
        );

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "User ID or Email already exists.";
            $message_type = "error";

        } else {

            // Hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users
                (user_id, name, email, phone, department, year, password, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssssss",
                $user_id,
                $name,
                $email,
                $phone,
                $department,
                $year,
                $hashed_password,
                $role
            );

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";
                $message_type = "success";

            } else {

                $message = "Registration failed. Please try again.";
                $message_type = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register - Smart College Event Hub</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<div class="form-page">

    <div class="form-container">

        <h1>Create Account</h1>

        <p class="form-subtitle">
            Join Smart College Event Hub
        </p>

        <?php if (!empty($message)): ?>

            <div class="form-message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST"
              action="register.php">

            <label>User ID</label>

            <input
                type="text"
                name="user_id"
                placeholder="Enter your ID"
                required
            >


            <label>Full Name</label>

            <input
                type="text"
                name="name"
                placeholder="Enter full name"
                required
            >


            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Enter email"
                required
            >


            <label>Phone</label>

            <input
                type="text"
                name="phone"
                placeholder="Enter phone number"
            >


            <label>Department</label>

            <input
                type="text"
                name="department"
                placeholder="Example: Computer Engineering"
            >


            <label>Year</label>

            <select name="year">

                <option value="">
                    Select Year
                </option>

                <option value="First Year">
                    First Year
                </option>

                <option value="Second Year">
                    Second Year
                </option>

                <option value="Third Year">
                    Third Year
                </option>

                <option value="Fourth Year">
                    Fourth Year
                </option>

            </select>


            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Minimum 6 characters"
                required
            >


            <label>Register As</label>

            <select name="role" required>

                <option value="">
                    Select Role
                </option>

                <option value="student">
                    Student
                </option>

                <option value="organizer">
                    Organizer
                </option>

            </select>


            <button type="submit"
                    class="form-button">

                Create Account

            </button>

        </form>


        <p class="form-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>


        <p class="form-link">

            <a href="index.php">
                ← Back to Home
            </a>

        </p>

    </div>

</div>

</body>

</html>