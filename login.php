<?php

session_start();

require_once "database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login_id = trim($_POST["login_id"]);
    $password = $_POST["password"];

    if (empty($login_id) || empty($password)) {

        $message = "Please enter User ID/Email and Password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, user_id, name, email, password, role
             FROM users
             WHERE user_id = ? OR email = ?"
        );

        $stmt->bind_param(
            "ss",
            $login_id,
            $login_id
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                // Store user information in session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_code"] = $user["user_id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                // Role-based redirect

                if ($user["role"] == "student") {

                    header("Location: student/dashboard.php");
                    exit;

                } elseif ($user["role"] == "organizer") {

                    header("Location: organizer/dashboard.php");
                    exit;

                } elseif ($user["role"] == "admin") {

                    header("Location: admin/dashboard.php");
                    exit;
                }

            } else {

                $message = "Incorrect password.";

            }

        } else {

            $message = "User not found.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Smart College Event Hub</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<div class="form-page">

    <div class="form-container">

        <h1>Login</h1>

        <p class="form-subtitle">
            Welcome back to Smart College Event Hub
        </p>


        <?php if (!empty($message)): ?>

            <div class="form-message error">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              action="login.php">

            <label>
                User ID or Email
            </label>

            <input
                type="text"
                name="login_id"
                placeholder="Enter User ID or Email"
                required
            >


            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                placeholder="Enter password"
                required
            >


            <button
                type="submit"
                class="form-button">

                Login

            </button>

        </form>


        <p class="form-link">

            Don't have an account?

            <a href="register.php">
                Register
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