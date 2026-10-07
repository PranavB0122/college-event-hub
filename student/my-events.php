<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: ../login.php");
    exit;
}

include("../database.php");

$student_id = $_SESSION["user_id"];

/* Get student's registrations and attendance */
$sql = "
    SELECT 
        registrations.id,
        registrations.registration_id,
        registrations.status AS registration_status,
        registrations.registered_at,

        events.event_id,
        events.title,
        events.category,
        events.event_date,
        events.event_time,
        events.venue,
        events.status AS event_status,

        attendance.attendance_status

    FROM registrations

    INNER JOIN events 
        ON registrations.event_id = events.id

    LEFT JOIN attendance
        ON attendance.registration_id = registrations.id

    WHERE registrations.student_id = ?

    ORDER BY events.event_date ASC, events.event_time ASC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Events - Smart College Event Hub</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        .my-events-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            margin-bottom: 25px;
        }

        .page-title h1 {
            margin-bottom: 8px;
        }

        .events-table-container {
            overflow-x: auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .events-table {
            width: 100%;
            border-collapse: collapse;
        }

        .events-table th,
        .events-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .events-table th {
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

        .attendance-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-top: 5px;
        }

        .present {
            background: #d4edda;
            color: #155724;
        }

        .absent {
            background: #f8d7da;
            color: #721c24;
        }

        .cancel-btn {
            display: inline-block;
            padding: 7px 12px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .cancel-btn:hover {
            background: #b02a37;
        }

        .feedback-btn {
            display: inline-block;
            padding: 7px 12px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
        }

        .feedback-btn:hover {
            background: #0056b3;
        }

        .no-events {
            text-align: center;
            padding: 50px 20px;
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

        .success-message {
            margin-bottom: 20px;
            padding: 14px 18px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            font-weight: 600;
        }

    </style>

</head>


<body>


    <!-- Navbar -->

    <nav class="navbar">

        <div class="nav-container">

            <div class="logo">
                Smart Event Hub
            </div>

            <div class="nav-links">

                <a href="dashboard.php">
                    Dashboard
                </a>

                <a href="events.php">
                    Events
                </a>

                <a href="my-events.php">
                    My Events
                </a>

                <a href="../logout.php">
                    Logout
                </a>

            </div>

        </div>

    </nav>


    <!-- Main Content -->

    <div class="my-events-container">


        <a href="dashboard.php"
           class="back-btn">

            ← Back to Dashboard

        </a>


        <div class="page-title">

            <h1>
                My Events
            </h1>

            <p>
                View all events you have registered for.
            </p>

        </div>


        <!-- Feedback Success Message -->

        <?php if (
            isset($_GET["feedback"]) &&
            $_GET["feedback"] == "success"
        ) { ?>

            <div class="success-message">

                Feedback submitted successfully!

            </div>

        <?php } ?>


        <?php if ($result->num_rows > 0) { ?>


            <div class="events-table-container">


                <table class="events-table">


                    <thead>

                        <tr>

                            <th>
                                Registration ID
                            </th>

                            <th>
                                Event
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Time
                            </th>

                            <th>
                                Venue
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Attendance
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while ($row = $result->fetch_assoc()) { ?>


                            <tr>


                                <!-- Registration ID -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["registration_id"]
                                    );
                                    ?>

                                </td>


                                <!-- Event -->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["title"]
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <!-- Category -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["category"]
                                    );
                                    ?>

                                </td>


                                <!-- Date -->

                                <td>

                                    <?php

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $row["event_date"]
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- Time -->

                                <td>

                                    <?php

                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $row["event_time"]
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- Venue -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["venue"]
                                    );
                                    ?>

                                </td>


                                <!-- Registration Status -->

                                <td>

                                    <span
                                        class="status-badge <?php echo strtolower($row["registration_status"]); ?>"
                                    >

                                        <?php

                                        echo ucfirst(
                                            $row["registration_status"]
                                        );

                                        ?>

                                    </span>

                                </td>


                                <!-- Attendance -->

                                <td>


                                    <?php

                                    if (
                                        $row["attendance_status"] == "present"
                                    ) {

                                    ?>

                                        <span class="attendance-badge present">

                                            Present

                                        </span>


                                    <?php

                                    } elseif (
                                        $row["attendance_status"] == "absent"
                                    ) {

                                    ?>

                                        <span class="attendance-badge absent">

                                            Absent

                                        </span>


                                    <?php

                                    } else {

                                        echo "-";

                                    }

                                    ?>


                                </td>


                                <!-- Action -->

                                <td>


                                    <?php

                                    /*
                                     * Show Feedback button
                                     * only when student is present.
                                     */

                                    if (
                                        $row["attendance_status"] == "present"
                                    ) {

                                    ?>

                                        <a
                                            href="feedback.php?id=<?php echo $row["event_id"]; ?>"
                                            class="feedback-btn"
                                        >

                                            Give Feedback

                                        </a>

                                        <br>


                                    <?php

                                    }


                                    /*
                                     * Show Cancel button
                                     * for confirmed or waiting registration.
                                     */

                                    if (
                                        $row["registration_status"] == "confirmed" ||
                                        $row["registration_status"] == "waiting"
                                    ) {

                                    ?>

                                        <a
                                            href="cancel-registration.php?id=<?php echo $row["id"]; ?>"
                                            class="cancel-btn"
                                            onclick="return confirm('Are you sure you want to cancel this registration?');"
                                        >

                                            Cancel

                                        </a>


                                    <?php

                                    }


                                    /*
                                     * If no action is available.
                                     */

                                    if (
                                        $row["attendance_status"] != "present" &&
                                        $row["registration_status"] != "confirmed" &&
                                        $row["registration_status"] != "waiting"
                                    ) {

                                        echo "-";

                                    }

                                    ?>


                                </td>


                            </tr>


                        <?php } ?>


                    </tbody>


                </table>


            </div>


        <?php } else { ?>


            <div class="events-table-container no-events">


                <h2>
                    No Registered Events
                </h2>


                <p>
                    You have not registered for any events yet.
                </p>


                <br>


                <a
                    href="events.php"
                    class="back-btn"
                >

                    Explore Events

                </a>


            </div>


        <?php } ?>


    </div>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?>