<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "admin"
) {
    header("Location: ../login.php");
    exit;
}

?>

<h1>Admin Dashboard</h1>

<p>
Welcome,
<?php echo htmlspecialchars($_SESSION["name"]); ?>
</p>

<a href="../logout.php">
    Logout
</a>