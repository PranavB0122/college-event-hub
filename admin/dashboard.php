<?php

session_start();
require_once "../database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit;
}

/* Total Students */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'student'"
);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()["total"];


/* Total Organizers */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'organizer'"
);
$stmt->execute();
$total_organizers = $stmt->get_result()->fetch_assoc()["total"];


/* Total Events */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM events"
);
$stmt->execute();
$total_events = $stmt->get_result()->fetch_assoc()["total"];


/* Pending Events */

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total 
     FROM events 
     WHERE status = 'pending'"
);
$stmt->execute();
$pending_events = $stmt->get_result()->fetch_assoc()["total"];


/* Pending Event List */

$sql = "SELECT 
            e.id,
            e.event_id,
            e.title,
            e.category,
            e.event_date,
            e.venue,
            u.name AS organizer_name
        FROM events e
        INNER JOIN users u
            ON e.organizer_id = u.id
        WHERE e.status = 'pending'
        ORDER BY e.created_at DESC";

$pending_list = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Smart Event Hub</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        Smart Event Hub
    </div>

    <div class="nav-links">

        <a href="../index.php">
            Home
        </a>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="../logout.php"
           class="login-btn">
            Logout
        </a>

    </div>

</nav>


<!-- ADMIN DASHBOARD -->

<section class="dashboard-section">

    <div class="dashboard-container">


        <!-- HEADER -->

        <div class="dashboard-header">

            <div>

                <h1>Admin Dashboard</h1>

                <p>
                    Welcome,
                    <?php
                    echo htmlspecialchars($_SESSION["name"]);
                    ?>!
                </p>

            </div>

        </div>


        <!-- STATISTICS -->

        <div class="dashboard-stats">


            <div class="dashboard-card">

                <h3>Total Students</h3>

                <div class="dashboard-number">
                    <?php echo $total_students; ?>
                </div>

            </div>


            <div class="dashboard-card">

                <h3>Total Organizers</h3>

                <div class="dashboard-number">
                    <?php echo $total_organizers; ?>
                </div>

            </div>


            <div class="dashboard-card">

                <h3>Total Events</h3>

                <div class="dashboard-number">
                    <?php echo $total_events; ?>
                </div>

            </div>


            <div class="dashboard-card">

                <h3>Pending Events</h3>

                <div class="dashboard-number">
                    <?php echo $pending_events; ?>
                </div>

            </div>


        </div>


        <!-- PENDING EVENTS -->

        <div class="dashboard-content">

            <h2>Pending Event Approvals</h2>


            <?php if ($pending_list->num_rows > 0): ?>


                <div class="table-container">

                    <table class="event-table">

                        <thead>

                            <tr>

                                <th>Event ID</th>

                                <th>Event Title</th>

                                <th>Category</th>

                                <th>Organizer</th>

                                <th>Date</th>

                                <th>Venue</th>

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php while ($event = $pending_list->fetch_assoc()): ?>


                                <tr>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $event["event_id"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $event["title"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $event["category"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $event["organizer_name"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $event["event_date"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $event["venue"]
                                        );
                                        ?>
                                    </td>


                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="approve-event.php?id=<?php echo $event["id"]; ?>"
                                                class="small-btn approve-btn"
                                            >
                                                Approve
                                            </a>


                                            <a
                                                href="reject-event.php?id=<?php echo $event["id"]; ?>"
                                                class="small-btn delete-btn"
                                                onclick="return confirm('Are you sure you want to reject this event?');"
                                            >
                                                Reject
                                            </a>

                                        </div>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="empty-state">

                    <h3>No Pending Events</h3>

                    <p>
                        There are currently no events waiting for approval.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </div>

</section>


</body>

</html>