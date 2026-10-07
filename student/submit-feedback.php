<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: ../login.php");
    exit;
}

include("../database.php");

$student_id = $_SESSION["user_id"];


/* Only POST request allowed */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my-events.php");
    exit;
}


/* Check event ID */

if (!isset($_POST["event_id"]) || !is_numeric($_POST["event_id"])) {
    die("Invalid event.");
}

$event_id = (int) $_POST["event_id"];


/* Get ratings */

$content_rating = isset($_POST["content_rating"])
    ? (int) $_POST["content_rating"]
    : 0;

$speaker_rating = isset($_POST["speaker_rating"])
    ? (int) $_POST["speaker_rating"]
    : 0;

$organization_rating = isset($_POST["organization_rating"])
    ? (int) $_POST["organization_rating"]
    : 0;

$overall_rating = isset($_POST["overall_rating"])
    ? (int) $_POST["overall_rating"]
    : 0;

$comments = isset($_POST["comments"])
    ? trim($_POST["comments"])
    : "";


/* Validate ratings */

if (
    $content_rating < 1 || $content_rating > 5 ||
    $speaker_rating < 1 || $speaker_rating > 5 ||
    $organization_rating < 1 || $organization_rating > 5 ||
    $overall_rating < 1 || $overall_rating > 5
) {
    die("Please select all ratings between 1 and 5.");
}


/* Check whether student was present */

$sql = "
    SELECT id
    FROM attendance
    WHERE event_id = ?
      AND student_id = ?
      AND attendance_status = 'present'
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "ii",
    $event_id,
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();
    $conn->close();

    die("You can submit feedback only if your attendance is marked Present.");
}

$stmt->close();


/* Check duplicate feedback */

$sql = "
    SELECT id
    FROM feedback
    WHERE event_id = ?
      AND student_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "ii",
    $event_id,
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();
    $conn->close();

    die("You have already submitted feedback for this event.");
}

$stmt->close();


/* Insert feedback */

$sql = "
    INSERT INTO feedback
    (
        event_id,
        student_id,
        content_rating,
        speaker_rating,
        organization_rating,
        overall_rating,
        comments
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "iiiiiis",
    $event_id,
    $student_id,
    $content_rating,
    $speaker_rating,
    $organization_rating,
    $overall_rating,
    $comments
);

if (!$stmt->execute()) {

    die("Feedback could not be saved: " . $stmt->error);
}

$stmt->close();
$conn->close();


/* Redirect after successful submission */

header("Location: my-events.php?feedback=success");
exit;

?>