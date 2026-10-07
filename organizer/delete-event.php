<?php

session_start();
require_once "../database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "organizer") {
    header("Location: ../login.php");
    exit;
}

$organizer_id = $_SESSION["user_id"];

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: dashboard.php");
    exit;
}

$event_id = intval($_GET["id"]);


/* Check event belongs to current organizer */

$sql = "SELECT id FROM events
        WHERE id = ? AND organizer_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $event_id,
    $organizer_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows == 0) {

    header("Location: dashboard.php");
    exit;
}


/* Delete Event */

$sql = "DELETE FROM events
        WHERE id = ? AND organizer_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $event_id,
    $organizer_id
);

$stmt->execute();


header("Location: dashboard.php");

exit;

?>