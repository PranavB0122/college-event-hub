<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "organizer") {
    header("Location: ../login.php");
    exit;
}

include("../database.php");

$organizer_id = $_SESSION["user_id"];


/* Check request */

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: dashboard.php");
    exit;
}


/* Get event ID */

if (!isset($_POST["event_id"]) || !is_numeric($_POST["event_id"])) {
    header("Location: dashboard.php");
    exit;
}

$event_id = (int) $_POST["event_id"];


/* Verify event belongs to organizer */

$sql = "
    SELECT id
    FROM events
    WHERE id = ?
      AND organizer_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();
    $conn->close();

    header("Location: dashboard.php");
    exit;
}

$stmt->close();


/* Check attendance data */

if (
    !isset($_POST["attendance_status"]) ||
    !is_array($_POST["attendance_status"])
) {
    header("Location: attendance.php?id=" . $event_id);
    exit;
}


$attendance_data = $_POST["attendance_status"];


/*
    Start transaction.
*/

$conn->begin_transaction();

try {

    foreach ($attendance_data as $registration_id => $status) {

        /* Validate status */

        if (
            $status != "present" &&
            $status != "absent"
        ) {
            continue;
        }


        /* Validate registration belongs to this event */

        $sql = "
            SELECT
                registrations.id,
                registrations.student_id
            FROM registrations
            WHERE registrations.id = ?
              AND registrations.event_id = ?
              AND registrations.status = 'confirmed'
        ";

        $stmt = $conn->prepare($sql);

        $registration_id = (int) $registration_id;

        $stmt->bind_param(
            "ii",
            $registration_id,
            $event_id
        );

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows == 0) {

            $stmt->close();

            continue;
        }


        $registration = $result->fetch_assoc();

        $student_id = $registration["student_id"];

        $stmt->close();


        /* Check if attendance already exists */

        $sql = "
            SELECT id
            FROM attendance
            WHERE registration_id = ?
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "i",
            $registration_id
        );

        $stmt->execute();

        $attendance_result = $stmt->get_result();


        if ($attendance_result->num_rows > 0) {

            /*
                Update existing attendance
            */

            $attendance = $attendance_result->fetch_assoc();

            $attendance_id = $attendance["id"];

            $stmt->close();


            $sql = "
                UPDATE attendance
                SET attendance_status = ?,
                    marked_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ";

            $update_stmt = $conn->prepare($sql);

            $update_stmt->bind_param(
                "si",
                $status,
                $attendance_id
            );

            $update_stmt->execute();

            $update_stmt->close();

        } else {

            /*
                Insert new attendance
            */

            $stmt->close();


            $sql = "
                INSERT INTO attendance
                (
                    registration_id,
                    student_id,
                    event_id,
                    attendance_status
                )
                VALUES (?, ?, ?, ?)
            ";

            $insert_stmt = $conn->prepare($sql);

            $insert_stmt->bind_param(
                "iiis",
                $registration_id,
                $student_id,
                $event_id,
                $status
            );

            $insert_stmt->execute();

            $insert_stmt->close();
        }
    }


    /* Save transaction */

    $conn->commit();


    /* Go back to attendance page */

    header(
        "Location: attendance.php?id=" . $event_id . "&saved=1"
    );

    exit;


} catch (Exception $e) {

    /* Undo changes if something goes wrong */

    $conn->rollback();

    die("Attendance could not be saved. Please try again.");

}

?>