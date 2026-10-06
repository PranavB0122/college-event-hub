<?php

session_start();

require_once "../database.php";

// Student authentication
if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "student"
) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION["user_id"];

// Total registrations
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM registrations
     WHERE student_id = ?
     AND status != 'cancelled'"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();
$registration_data = $result->fetch_assoc();

$total_registrations = $registration_data["total"];

$stmt->close();


// Confirmed registrations
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM registrations
     WHERE student_id = ?
     AND status = 'confirmed'"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();
$confirmed_data = $result->fetch_assoc();

$total_confirmed = $confirmed_data["total"];

$stmt->close();


// Waiting list count
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM registrations
     WHERE student_id = ?
     AND status = 'waiting'"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();
$waiting_data = $result->fetch_assoc();

$total_waiting = $waiting_data["total"];

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<!-- NAVBAR -->

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

        <a href="#">
            My Events
        </a>

        <a href="profile.php">
            Profile
        </a>

        <a href="../logout.php"
           class="logout-btn">
            Logout
        </a>

    </nav>

</header>


<!-- DASHBOARD -->

<main class="dashboard-container">

    <div class="dashboard-header">

        <div>

            <p class="dashboard-label">
                STUDENT DASHBOARD
            </p>

            <h1>
                Welcome,
                <?php
                echo htmlspecialchars($_SESSION["name"]);
                ?>
            </h1>

            <p>
                Discover and participate in college events.
            </p>

        </div>

    </div>


    <!-- STATISTICS -->

    <section class="dashboard-stats">

        <div class="dashboard-stat-card">

            <h3>
                <?php echo $total_registrations; ?>
            </h3>

            <p>
                Total Registrations
            </p>

        </div>


        <div class="dashboard-stat-card">

            <h3>
                <?php echo $total_confirmed; ?>
            </h3>

            <p>
                Confirmed Events
            </p>

        </div>


        <div class="dashboard-stat-card">

            <h3>
                <?php echo $total_waiting; ?>
            </h3>

            <p>
                Waiting List
            </p>

        </div>


        <div class="dashboard-stat-card">

            <h3>
                Explore
            </h3>

            <p>
                New Events
            </p>

        </div>

    </section>


    <!-- QUICK ACTIONS -->

    <section class="dashboard-section">

        <h2>
            Quick Actions
        </h2>

        <div class="quick-actions">

            <a href="events.php"
               class="quick-action">

                <span>🔍</span>

                <div>

                    <h3>
                        Explore Events
                    </h3>

                    <p>
                        Find upcoming college events.
                    </p>

                </div>

            </a>


            <a href="events.php"
               class="quick-action">

                <span>🎓</span>

                <div>

                    <h3>
                        Register for Event
                    </h3>

                    <p>
                        Join an event you are interested in.
                    </p>

                </div>

            </a>

        </div>

    </section>


    <!-- UPCOMING EVENTS -->

    <section class="dashboard-section">

        <div class="section-title-row">

            <h2>
                Upcoming Events
            </h2>

            <a href="events.php">
                View All
            </a>

        </div>


        <div class="event-grid">

        <?php

        $today = date("Y-m-d");

        $stmt = $conn->prepare(
            "SELECT
                e.id,
                e.event_id,
                e.title,
                e.category,
                e.event_date,
                e.event_time,
                e.venue,
                e.capacity,
                u.name AS organizer_name,

                (
                    SELECT COUNT(*)
                    FROM registrations r
                    WHERE r.event_id = e.id
                    AND r.status = 'confirmed'
                ) AS confirmed_count

            FROM events e

            JOIN users u
                ON e.organizer_id = u.id

            WHERE e.status = 'approved'
            AND e.event_date >= ?

            ORDER BY e.event_date ASC

            LIMIT 6"
        );

        $stmt->bind_param("s", $today);

        $stmt->execute();

        $events = $stmt->get_result();


        if ($events->num_rows > 0):

            while ($event = $events->fetch_assoc()):

                $available =
                    $event["capacity"] -
                    $event["confirmed_count"];

        ?>

            <div class="event-card">

                <div class="event-category">

                    <?php
                    echo htmlspecialchars(
                        $event["category"]
                    );
                    ?>

                </div>


                <h3>

                    <?php
                    echo htmlspecialchars(
                        $event["title"]
                    );
                    ?>

                </h3>


                <div class="event-info">

                    <p>
                        📅
                        <?php
                        echo date(
                            "d M Y",
                            strtotime($event["event_date"])
                        );
                        ?>
                    </p>

                    <p>
                        ⏰
                        <?php
                        echo date(
                            "h:i A",
                            strtotime($event["event_time"])
                        );
                        ?>
                    </p>

                    <p>
                        📍
                        <?php
                        echo htmlspecialchars(
                            $event["venue"]
                        );
                        ?>
                    </p>

                </div>


                <p class="event-seats">

                    <?php
                    echo $available;
                    ?>
                    seats available

                </p>


                <a
                    href="event-details.php?id=<?php echo $event["id"]; ?>"
                    class="event-button">

                    View Details

                </a>

            </div>

        <?php

            endwhile;

        else:

        ?>

            <div class="no-events">

                <h3>
                    No upcoming events
                </h3>

                <p>
                    New events will appear here
                    after admin approval.
                </p>

            </div>

        <?php endif; ?>

        </div>

    </section>

</main>

</body>

</html>