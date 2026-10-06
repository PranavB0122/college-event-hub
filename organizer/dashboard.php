<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "organizer"
) {
    header("Location: ../login.php");
    exit;
}

?>

<h1>Organizer Dashboard</h1>

<p>
Welcome,
<?php echo htmlspecialchars($_SESSION["name"]); ?>
</p>

<a href="../logout.php">
    Logout
</a>