<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "organizer") {
    header("Location: ../login.php");
    exit;
}

include("../database.php");

$organizer_id = $_SESSION["user_id"];
$organizer_name = $_SESSION["name"];


/* =========================
   DASHBOARD STATISTICS
   ========================= */

/* Total Events */

$sql = "
    SELECT COUNT(*) AS total_events
    FROM events
    WHERE organizer_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();

$result = $stmt->get_result();
$total_events = $result->fetch_assoc()["total_events"];

$stmt->close();


/* Pending Events */

$sql = "
    SELECT COUNT(*) AS pending_events
    FROM events
    WHERE organizer_id = ?
      AND status = 'pending'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();

$result = $stmt->get_result();
$pending_events = $result->fetch_assoc()["pending_events"];

$stmt->close();


/* Approved Events */

$sql = "
    SELECT COUNT(*) AS approved_events
    FROM events
    WHERE organizer_id = ?
      AND status = 'approved'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();

$result = $stmt->get_result();
$approved_events = $result->fetch_assoc()["approved_events"];

$stmt->close();


/* Total Registrations */

$sql = "
    SELECT COUNT(*) AS total_registrations
    FROM registrations
    INNER JOIN events
        ON registrations.event_id = events.id
    WHERE events.organizer_id = ?
      AND registrations.status IN ('confirmed', 'waiting')
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();

$result = $stmt->get_result();
$total_registrations = $result->fetch_assoc()["total_registrations"];

$stmt->close();


/* =========================
   ORGANIZER EVENTS
   ========================= */

$sql = "
    SELECT
        events.id,
        events.event_id,
        events.title,
        events.category,
        events.event_date,
        events.event_time,
        events.venue,
        events.capacity,
        events.status,

        (
            SELECT COUNT(*)
            FROM registrations
            WHERE registrations.event_id = events.id
              AND registrations.status = 'confirmed'
        ) AS confirmed_count,

        (
            SELECT COUNT(*)
            FROM registrations
            WHERE registrations.event_id = events.id
              AND registrations.status = 'waiting'
        ) AS waiting_count

    FROM events

    WHERE events.organizer_id = ?

    ORDER BY events.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();

$events = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Organizer Dashboard - Smart College Event Hub</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-section {
            margin-bottom: 30px;
        }

        .welcome-section h1 {
            margin-bottom: 8px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .stat-card h2 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .stat-card p {
            margin: 0;
            color: #666;
        }

        .dashboard-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .dashboard-btn {
            display: inline-block;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 7px;
            color: white;
            background: #007bff;
        }

        .dashboard-btn:hover {
            background: #0056b3;
        }

        .events-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .events-table-container {
            overflow-x: auto;
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .events-table th,
        .events-table td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .events-table th {
            background: #f5f5f5;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .approved {
            background: #d4edda;
            color: #155724;
        }

        .rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-block;
            padding: 7px 10px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            font-size: 12px;
        }

        .participants-btn {
            background: #28a745;
        }

        .participants-btn:hover {
            background: #218838;
        }

        .edit-btn {
            background: #007bff;
        }

        .edit-btn:hover {
            background: #0056b3;
        }

        .delete-btn {
            background: #dc3545;
        }

        .delete-btn:hover {
            background: #b02a37;
        }

        .no-events {
            text-align: center;
            padding: 40px;
        }

        @media (max-width: 900px) {

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-actions {
                flex-direction: column;
            }

        }

    </style>

</head>


<body>


<!-- NAVBAR -->

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


<!-- DASHBOARD -->

<div class="dashboard-container">


    <!-- Welcome -->

    <div class="welcome-section">

        <h1>
            Welcome, <?php echo htmlspecialchars($organizer_name); ?>!
        </h1>

        <p>
            Manage your college events and participants.
        </p>

    </div>


    <!-- STATISTICS -->

    <div class="stats-grid">

        <div class="stat-card">

            <h2>
                <?php echo $total_events; ?>
            </h2>

            <p>
                Total Events
            </p>

        </div>


        <div class="stat-card">

            <h2>
                <?php echo $pending_events; ?>
            </h2>

            <p>
                Pending Events
            </p>

        </div>


        <div class="stat-card">

            <h2>
                <?php echo $approved_events; ?>
            </h2>

            <p>
                Approved Events
            </p>

        </div>


        <div class="stat-card">

            <h2>
                <?php echo $total_registrations; ?>
            </h2>

            <p>
                Registrations
            </p>

        </div>

    </div>


    <!-- ACTIONS -->

    <div class="dashboard-actions">

        <a
            href="create-event.php"
            class="dashboard-btn"
        >
            + Create New Event
        </a>

    </div>


    <!-- EVENTS -->

    <div class="events-section">

        <h2>
            My Events
        </h2>


        <?php if ($events->num_rows > 0) { ?>


            <div class="events-table-container">

                <table class="events-table">

                    <thead>

                        <tr>

                            <th>Event</th>

                            <th>Category</th>

                            <th>Date</th>

                            <th>Venue</th>

                            <th>Capacity</th>

                            <th>Confirmed</th>

                            <th>Waiting</th>

                            <th>Status</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($row = $events->fetch_assoc()) { ?>


                        <tr>


                            <!-- EVENT -->

                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["title"]
                                    );
                                    ?>
                                </strong>

                                <br>

                                <small>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["event_id"]
                                    );
                                    ?>
                                </small>

                            </td>


                            <!-- CATEGORY -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["category"]
                                );
                                ?>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $row["event_date"]
                                    )
                                );
                                ?>

                                <br>

                                <small>

                                    <?php
                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $row["event_time"]
                                        )
                                    );
                                    ?>

                                </small>

                            </td>


                            <!-- VENUE -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["venue"]
                                );
                                ?>

                            </td>


                            <!-- CAPACITY -->

                            <td>

                                <?php
                                echo $row["capacity"];
                                ?>

                            </td>


                            <!-- CONFIRMED -->

                            <td>

                                <?php
                                echo $row["confirmed_count"];
                                ?>

                            </td>


                            <!-- WAITING -->

                            <td>

                                <?php
                                echo $row["waiting_count"];
                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span class="status-badge
                                    <?php
                                    echo strtolower(
                                        $row["status"]
                                    );
                                    ?>">

                                    <?php
                                    echo ucfirst(
                                        $row["status"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div class="action-buttons">


                                    <a
                                        href="participants.php?id=<?php echo (int)$row["id"]; ?>"
                                        class="action-btn participants-btn"
                                    >
                                        Participants
                                    </a>

                                    <a
                                        href="attendance.php?id=<?php echo (int)$row["id"]; ?>"
                                        class="action-btn attendance-btn"
                                    >
                                        Attendance
                                    </a>


                                    <a
                                        href="edit-event.php?id=<?php echo (int)$row["id"]; ?>"
                                        class="action-btn edit-btn"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="delete-event.php?id=<?php echo (int)$row["id"]; ?>"
                                        class="action-btn delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this event?');"
                                    >
                                        Delete
                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php } ?>


                    </tbody>

                </table>

            </div>


        <?php } else { ?>


            <div class="no-events">

                <h3>
                    No Events Yet
                </h3>

                <p>
                    You have not created any events yet.
                </p>

                <br>

                <a
                    href="create-event.php"
                    class="dashboard-btn"
                >
                    Create Your First Event
                </a>

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