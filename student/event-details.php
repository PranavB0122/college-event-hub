<?php

session_start();

require_once "../database.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: ../login.php");
    exit;
}


$event_id = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


if ($event_id <= 0) {
    die("Invalid event.");
}


$stmt = $conn->prepare(
    "SELECT
        e.*,
        u.name AS organizer_name

     FROM events e

     JOIN users u
        ON e.organizer_id = u.id

     WHERE e.id = ?
     AND e.status = 'approved'"
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


// Count confirmed seats

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

$confirmed = $data["total"];

$stmt->close();


$available =
    $event["capacity"] - $confirmed;


// Check current student's registration

$stmt = $conn->prepare(
    "SELECT status
     FROM registrations
     WHERE event_id = ?
     AND student_id = ?"
);

$stmt->bind_param(
    "ii",
    $event_id,
    $_SESSION["user_id"]
);

$stmt->execute();

$registration_result =
    $stmt->get_result();

$registration_status = null;


if ($registration_result->num_rows == 1) {

    $registration =
        $registration_result->fetch_assoc();

    $registration_status =
        $registration["status"];
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($event["title"]); ?>
    </title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<header class="dashboard-navbar">

    <div class="logo">
        Smart Event Hub
    </div>

    <nav>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="events.php">
            Explore Events
        </a>

        <a href="../logout.php"
           class="logout-btn">
            Logout
        </a>

    </nav>

</header>


<main class="dashboard-container">

    <div class="event-details">

        <div class="event-details-header">

            <span class="event-category">

                <?php
                echo htmlspecialchars(
                    $event["category"]
                );
                ?>

            </span>


            <h1>

                <?php
                echo htmlspecialchars(
                    $event["title"]
                );
                ?>

            </h1>

        </div>


        <div class="event-details-content">

            <h2>
                About This Event
            </h2>

            <p>

                <?php
                echo nl2br(
                    htmlspecialchars(
                        $event["description"]
                    )
                );
                ?>

            </p>


            <div class="details-grid">

                <div>
                    <strong>Date</strong>

                    <p>
                        <?php
                        echo date(
                            "d M Y",
                            strtotime(
                                $event["event_date"]
                            )
                        );
                        ?>
                    </p>
                </div>


                <div>
                    <strong>Time</strong>

                    <p>
                        <?php
                        echo date(
                            "h:i A",
                            strtotime(
                                $event["event_time"]
                            )
                        );
                        ?>
                    </p>
                </div>


                <div>
                    <strong>Venue</strong>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $event["venue"]
                        );
                        ?>
                    </p>
                </div>


                <div>
                    <strong>Organizer</strong>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $event["organizer_name"]
                        );
                        ?>
                    </p>
                </div>


                <div>
                    <strong>Capacity</strong>

                    <p>
                        <?php
                        echo $event["capacity"];
                        ?>
                    </p>
                </div>


                <div>
                    <strong>Available Seats</strong>

                    <p>
                        <?php
                        echo max(0, $available);
                        ?>
                    </p>
                </div>

            </div>


            <div class="registration-box">

                <?php if ($registration_status == "confirmed"): ?>

                    <div class="status-confirmed">
                        ✓ You are registered for this event.
                    </div>


                <?php elseif ($registration_status == "waiting"): ?>

                    <div class="status-waiting">
                        ⏳ You are on the waiting list.
                    </div>


                <?php elseif ($registration_status == "cancelled"): ?>

                    <div class="status-cancelled">
                        Registration was cancelled.
                    </div>


                <?php elseif ($available > 0): ?>

                    <a
                        href="register-event.php?id=<?php echo $event_id; ?>"
                        class="primary-btn">

                        Register Now

                    </a>


                <?php else: ?>

                    <div class="status-full">

                        Registration Full

                    </div>


                    <a
                        href="register-event.php?id=<?php echo $event_id; ?>"
                        class="secondary-btn">

                        Join Waiting List

                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>

</body>

</html>