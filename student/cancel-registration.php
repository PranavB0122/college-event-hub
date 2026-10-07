<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: ../login.php");
    exit;
}

include("../database.php");

$student_id = $_SESSION["user_id"];

/* Check registration ID */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: my-events.php");
    exit;
}

$registration_id = (int) $_GET["id"];

/* Get registration details */
$sql = "
    SELECT 
        registrations.id,
        registrations.event_id,
        registrations.student_id,
        registrations.status,
        events.title,
        events.event_date,
        events.event_time
    FROM registrations
    INNER JOIN events
        ON registrations.event_id = events.id
    WHERE registrations.id = ?
      AND registrations.student_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $registration_id, $student_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();

    header("Location: my-events.php");
    exit;
}

$registration = $result->fetch_assoc();

$stmt->close();


/* Only confirmed or waiting registration can be cancelled */

if (
    $registration["status"] != "confirmed" &&
    $registration["status"] != "waiting"
) {
    header("Location: my-events.php");
    exit;
}


/* Event ID */
$event_id = $registration["event_id"];


/*
    Start transaction.

    This keeps cancellation and waiting-list promotion
    as one database operation.
*/

$conn->begin_transaction();

try {

    /* Cancel current student's registration */

    $sql = "
        UPDATE registrations
        SET status = 'cancelled'
        WHERE id = ?
          AND student_id = ?
          AND status IN ('confirmed', 'waiting')
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $registration_id, $student_id);
    $stmt->execute();

    $stmt->close();


    /*
        If the cancelled registration was CONFIRMED,
        promote the first waiting student.
    */

    if ($registration["status"] == "confirmed") {

        $sql = "
            SELECT id
            FROM registrations
            WHERE event_id = ?
              AND status = 'waiting'
            ORDER BY registered_at ASC, id ASC
            LIMIT 1
            FOR UPDATE
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();

        $waiting_result = $stmt->get_result();


        /* If waiting student exists */

        if ($waiting_result->num_rows > 0) {

            $waiting_student = $waiting_result->fetch_assoc();

            $waiting_registration_id = $waiting_student["id"];


            /* Promote waiting student */

            $update_sql = "
                UPDATE registrations
                SET status = 'confirmed'
                WHERE id = ?
                  AND status = 'waiting'
            ";

            $update_stmt = $conn->prepare($update_sql);

            $update_stmt->bind_param(
                "i",
                $waiting_registration_id
            );

            $update_stmt->execute();

            $update_stmt->close();
        }

        $stmt->close();
    }


    /* Save all changes */

    $conn->commit();


    /* Redirect */

    header("Location: my-events.php");
    exit;

} catch (Exception $e) {

    /* Undo changes if error occurs */

    $conn->rollback();

    die("Cancellation failed. Please try again.");

}

?>