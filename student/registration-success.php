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


$event_id = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


if ($event_id <= 0) {
    die("Invalid event.");
}


/*
|--------------------------------------------------------------------------
| Get Registration Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        r.registration_id,
        r.status,
        e.title,
        e.event_date,
        e.event_time,
        e.venue

     FROM registrations r

     JOIN events e
        ON r.event_id = e.id

     WHERE r.event_id = ?
     AND r.student_id = ?

     ORDER BY r.id DESC

     LIMIT 1"
);

$stmt->bind_param(
    "ii",
    $event_id,
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows != 1) {

    die("Registration not found.");

}


$registration =
    $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Registration Status
    </title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<div class="form-page">

    <div class="registration-result">

        <?php if ($registration["status"] == "confirmed"): ?>

            <div class="result-icon">
                ✓
            </div>

            <h1>
                Registration Confirmed
            </h1>

            <p>
                You have successfully registered
                for this event.
            </p>

        <?php else: ?>

            <div class="result-icon">
                ⏳
            </div>

            <h1>
                Added to Waiting List
            </h1>

            <p>
                All seats are currently full.
                You have been added to the waiting list.
            </p>

        <?php endif; ?>


        <div class="registration-info">

            <p>
                <strong>Event:</strong>

                <?php
                echo htmlspecialchars(
                    $registration["title"]
                );
                ?>

            </p>


            <p>
                <strong>Registration ID:</strong>

                <?php
                echo htmlspecialchars(
                    $registration["registration_id"]
                );
                ?>

            </p>


            <p>
                <strong>Status:</strong>

                <?php
                echo strtoupper(
                    htmlspecialchars(
                        $registration["status"]
                    )
                );
                ?>

            </p>


            <p>
                <strong>Date:</strong>

                <?php
                echo date(
                    "d M Y",
                    strtotime(
                        $registration["event_date"]
                    )
                );
                ?>

            </p>


            <p>
                <strong>Time:</strong>

                <?php
                echo date(
                    "h:i A",
                    strtotime(
                        $registration["event_time"]
                    )
                );
                ?>

            </p>


            <p>
                <strong>Venue:</strong>

                <?php
                echo htmlspecialchars(
                    $registration["venue"]
                );
                ?>

            </p>

        </div>


        <div class="result-buttons">

            <a
                href="dashboard.php"
                class="primary-btn">

                Dashboard

            </a>


            <a
                href="events.php"
                class="secondary-btn">

                Explore Events

            </a>

        </div>

    </div>

</div>

</body>

</html>