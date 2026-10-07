<?php

session_start();
require_once "../database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: dashboard.php");
    exit;
}

$event_id = intval($_GET["id"]);

$stmt = $conn->prepare(
    "UPDATE events
     SET status = 'rejected'
     WHERE id = ? AND status = 'pending'"
);

$stmt->bind_param("i", $event_id);

$stmt->execute();

header("Location: dashboard.php");
exit;

?>