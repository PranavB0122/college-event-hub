<?php

session_start();

require_once "../database.php";


/*
|--------------------------------------------------------------------------
| Student Authentication
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: ../login.php");
    exit;
}


$student_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Get Event ID
|--------------------------------------------------------------------------
*/

$event_id = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


if ($event_id <= 0) {
    die("Invalid event.");
}


/*
|--------------------------------------------------------------------------
| Get Event Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id, event_id, title, capacity, status
     FROM events
     WHERE id = ?
     AND status = 'approved'"
);

$stmt->bind_param(
    "i",
    $event_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows != 1) {
    die("Event not found.");
}


$event = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Check Existing Registration
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id, registration_id, status
     FROM registrations
     WHERE event_id = ?
     AND student_id = ?"
);

$stmt->bind_param(
    "ii",
    $event_id,
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows > 0) {

    $existing =
        $result->fetch_assoc();

    $stmt->close();

    header(
        "Location: event-details.php?id="
        . $event_id
    );

    exit;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Count Confirmed Registrations
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM registrations
     WHERE event_id = ?
     AND status = 'confirmed'"
);

$stmt->bind_param(
    "i",
    $event_id
);

$stmt->execute();

$result = $stmt->get_result();

$data = $result->fetch_assoc();

$confirmed_count =
    $data["total"];

$stmt->close();


/*
|--------------------------------------------------------------------------
| Decide Registration Status
|--------------------------------------------------------------------------
*/

if ($confirmed_count < $event["capacity"]) {

    $registration_status = "confirmed";

} else {

    $registration_status = "waiting";
}


/*
|--------------------------------------------------------------------------
| Generate Registration ID
|--------------------------------------------------------------------------
*/

$registration_id =
    "EVT"
    . date("YmdHis")
    . rand(100, 999);


/*
|--------------------------------------------------------------------------
| Insert Registration
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "INSERT INTO registrations
    (
        registration_id,
        event_id,
        student_id,
        status
    )
    VALUES (?, ?, ?, ?)"
);

$stmt->bind_param(
    "siis",
    $registration_id,
    $event_id,
    $student_id,
    $registration_status
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: registration-success.php?id="
        . $event_id
    );

    exit;

} else {

    $error =
        "Registration failed. Please try again.";

    $stmt->close();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Registration Error
    </title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<div class="form-page">

    <div class="form-container">

        <h1>
            Registration Failed
        </h1>

        <p>
            <?php
            echo htmlspecialchars($error);
            ?>
        </p>

        <br>

        <a
            href="event-details.php?id=<?php echo $event_id; ?>">

            Back to Event

        </a>

    </div>

</div>

</body>

</html>