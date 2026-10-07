<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "organizer") {
    header("Location: ../login.php");
    exit;
}

include("../database.php");

$organizer_id = $_SESSION["user_id"];

/* Check event ID */
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: dashboard.php");
    exit;
}

$event_id = (int) $_GET["id"];


/* Get event details and verify ownership */

$sql = "
    SELECT id, title, event_id, event_date, event_time, venue
    FROM events
    WHERE id = ?
      AND organizer_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();

$event_result = $stmt->get_result();

if ($event_result->num_rows == 0) {
    $stmt->close();
    $conn->close();

    header("Location: dashboard.php");
    exit;
}

$event = $event_result->fetch_assoc();

$stmt->close();


/* Get registered participants */

$sql = "
    SELECT
        registrations.id,
        registrations.registration_id,
        registrations.status AS registration_status,
        registrations.registered_at,
        users.user_id,
        users.name,
        users.email,
        users.phone,
        users.department,
        users.year
    FROM registrations
    INNER JOIN users
        ON registrations.student_id = users.id
    WHERE registrations.event_id = ?
    ORDER BY registrations.registered_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();

$participants = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Event Participants - Smart College Event Hub</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        .participants-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .event-info {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .event-info h1 {
            margin-bottom: 15px;
        }

        .event-info p {
            margin: 7px 0;
        }

        .participants-table-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .participants-table {
            width: 100%;
            border-collapse: collapse;
        }

        .participants-table th,
        .participants-table td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .participants-table th {
            background: #f5f5f5;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
        }

        .confirmed {
            background: #d4edda;
            color: #155724;
        }

        .waiting {
            background: #fff3cd;
            color: #856404;
        }

        .cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 18px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .back-btn:hover {
            background: #555;
        }

        .no-participants {
            text-align: center;
            padding: 40px 20px;
        }

    </style>

</head>

<body>


<!-- Navbar -->

<nav class="navbar">

    <div class="logo">
        Smart Event Hub
    </div>

    <div class="nav-links">

        <a href="../index.php">Home</a>

        <a href="dashboard.php">Dashboard</a>

        <a href="create-event.php">Create Event</a>

        <a href="../logout.php" class="login-btn">
            Logout
        </a>

    </div>

</nav>


<!-- Main Content -->

<div class="participants-container">


    <a href="dashboard.php" class="back-btn">
        ← Back to Dashboard
    </a>


    <!-- Event Information -->

    <div class="event-info">

        <h1>
            <?php echo htmlspecialchars($event["title"]); ?>
        </h1>

        <p>
            <strong>Event ID:</strong>
            <?php echo htmlspecialchars($event["event_id"]); ?>
        </p>

        <p>
            <strong>Date:</strong>
            <?php
            echo date(
                "d M Y",
                strtotime($event["event_date"])
            );
            ?>
        </p>

        <p>
            <strong>Time:</strong>
            <?php
            echo date(
                "h:i A",
                strtotime($event["event_time"])
            );
            ?>
        </p>

        <p>
            <strong>Venue:</strong>
            <?php echo htmlspecialchars($event["venue"]); ?>
        </p>

    </div>


    <!-- Participants -->

    <div class="participants-table-container">

        <h2>Registered Participants</h2>

        <br>


        <?php if ($participants->num_rows > 0) { ?>


            <table class="participants-table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Student Name</th>

                        <th>User ID</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>Department</th>

                        <th>Year</th>

                        <th>Registration ID</th>

                        <th>Status</th>

                        <th>Registered On</th>

                    </tr>

                </thead>


                <tbody>

                    <?php

                    $count = 1;

                    while ($row = $participants->fetch_assoc()) {

                    ?>

                        <tr>

                            <td>
                                <?php echo $count; ?>
                            </td>

                            <td>
                                <strong>
                                    <?php
                                    echo htmlspecialchars($row["name"]);
                                    ?>
                                </strong>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars($row["user_id"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars($row["email"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars($row["phone"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars($row["department"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars($row["year"]);
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["registration_id"]
                                );
                                ?>
                            </td>

                            <td>

                                <span class="status-badge
                                    <?php
                                    echo strtolower(
                                        $row["registration_status"]
                                    );
                                    ?>">

                                    <?php
                                    echo ucfirst(
                                        $row["registration_status"]
                                    );
                                    ?>

                                </span>

                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d M Y, h:i A",
                                    strtotime($row["registered_at"])
                                );
                                ?>
                            </td>

                        </tr>

                    <?php

                        $count++;

                    }

                    ?>

                </tbody>

            </table>


        <?php } else { ?>


            <div class="no-participants">

                <h3>No Participants Yet</h3>

                <p>
                    No students have registered for this event.
                </p>

            </div>


        <?php } ?>


    </div>

</div>


</body>

</html>

<?php

$stmt->close();
$conn->close();

?>