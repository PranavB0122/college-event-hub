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

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Explore Events</title>

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

    <div class="dashboard-header">

        <p class="dashboard-label">
            EVENT DISCOVERY
        </p>

        <h1>
            Explore Events
        </h1>

        <p>
            Find events and register for your favourite activities.
        </p>

    </div>


    <!-- SEARCH -->

    <form method="GET"
          class="event-filter">

        <input
            type="text"
            name="search"
            placeholder="Search events..."
            value="<?php echo htmlspecialchars($search); ?>"
        >


        <select name="category">

            <option value="">
                All Categories
            </option>

            <option value="Hackathon"
                <?php
                if ($category == "Hackathon")
                    echo "selected";
                ?>>
                Hackathon
            </option>

            <option value="Coding Competition"
                <?php
                if ($category == "Coding Competition")
                    echo "selected";
                ?>>
                Coding Competition
            </option>

            <option value="Workshop"
                <?php
                if ($category == "Workshop")
                    echo "selected";
                ?>>
                Workshop
            </option>

            <option value="Seminar"
                <?php
                if ($category == "Seminar")
                    echo "selected";
                ?>>
                Seminar
            </option>

            <option value="Cultural"
                <?php
                if ($category == "Cultural")
                    echo "selected";
                ?>>
                Cultural
            </option>

            <option value="Sports"
                <?php
                if ($category == "Sports")
                    echo "selected";
                ?>>
                Sports
            </option>

            <option value="Technical"
                <?php
                if ($category == "Technical")
                    echo "selected";
                ?>>
                Technical
            </option>

            <option value="Other"
                <?php
                if ($category == "Other")
                    echo "selected";
                ?>>
                Other
            </option>

        </select>


        <button type="submit"
                class="form-button">

            Search

        </button>

    </form>


    <!-- EVENTS -->

    <div class="event-grid">

    <?php

    $today = date("Y-m-d");

    $sql = "
        SELECT
            e.id,
            e.title,
            e.description,
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
    ";


    $params = [$today];
    $types = "s";


    if (!empty($search)) {

        $sql .= "
            AND (
                e.title LIKE ?
                OR e.description LIKE ?
                OR e.category LIKE ?
            )
        ";

        $search_value = "%" . $search . "%";

        $params[] = $search_value;
        $params[] = $search_value;
        $params[] = $search_value;

        $types .= "sss";
    }


    if (!empty($category)) {

        $sql .= " AND e.category = ? ";

        $params[] = $category;

        $types .= "s";
    }


    $sql .= "
        ORDER BY e.event_date ASC
    ";


    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        $types,
        ...$params
    );

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


            <p class="event-description">

                <?php

                $description =
                    $event["description"];

                if (strlen($description) > 120) {

                    $description =
                        substr($description, 0, 120)
                        . "...";
                }

                echo htmlspecialchars($description);

                ?>

            </p>


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

                <?php echo $available; ?>

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
                No events found
            </h3>

            <p>
                Try another search or category.
            </p>

        </div>

    <?php endif; ?>

    </div>

</main>

</body>

</html>