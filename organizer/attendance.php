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


/* Verify event belongs to organizer */

$sql = "
    SELECT id, event_id, title, event_date, event_time, venue
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


/* Get confirmed participants */

$sql = "
    SELECT
        registrations.id AS registration_id,
        registrations.registration_id AS registration_code,
        users.id AS student_id,
        users.user_id,
        users.name,
        users.email,

        attendance.attendance_status

    FROM registrations

    INNER JOIN users
        ON registrations.student_id = users.id

    LEFT JOIN attendance
        ON attendance.registration_id = registrations.id

    WHERE registrations.event_id = ?
      AND registrations.status = 'confirmed'

    ORDER BY users.name ASC
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

    <title>Attendance - Smart College Event Hub</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        .attendance-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .event-info {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .event-info h1 {
            margin-bottom: 15px;
        }

        .event-info p {
            margin: 7px 0;
        }

        .attendance-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .attendance-table th,
        .attendance-table td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .attendance-table th {
            background: #f5f5f5;
        }

        .attendance-select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .save-btn {
            margin-top: 20px;
            padding: 12px 22px;
            border: none;
            border-radius: 7px;
            background: #28a745;
            color: white;
            cursor: pointer;
            font-size: 15px;
        }

        .save-btn:hover {
            background: #218838;
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
            padding: 40px;
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

<div class="attendance-container">


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


    <!-- Attendance -->

    <div class="attendance-box">

        <h2>
            Mark Attendance
        </h2>

        <br>


        <?php if ($participants->num_rows > 0) { ?>


            <form
                method="POST"
                action="save-attendance.php"
            >

                <input
                    type="hidden"
                    name="event_id"
                    value="<?php echo $event_id; ?>"
                >


                <table class="attendance-table">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Student Name</th>

                            <th>User ID</th>

                            <th>Email</th>

                            <th>Registration ID</th>

                            <th>Attendance</th>

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
                                    echo htmlspecialchars(
                                        $row["name"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["user_id"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["email"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["registration_code"]
                                );
                                ?>

                            </td>


                            <td>

                                <input
                                    type="hidden"
                                    name="registration_ids[]"
                                    value="<?php echo $row["registration_id"]; ?>"
                                >


                                <select
                                    name="attendance_status[<?php echo $row["registration_id"]; ?>]"
                                    class="attendance-select"
                                    required
                                >

                                    <option value="">
                                        Select
                                    </option>

                                    <option
                                        value="present"
                                        <?php
                                        if ($row["attendance_status"] == "present") {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Present
                                    </option>

                                    <option
                                        value="absent"
                                        <?php
                                        if ($row["attendance_status"] == "absent") {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Absent
                                    </option>

                                </select>

                            </td>


                        </tr>


                    <?php

                        $count++;

                    }

                    ?>


                    </tbody>

                </table>


                <button
                    type="submit"
                    class="save-btn"
                >
                    Save Attendance
                </button>


            </form>


        <?php } else { ?>


            <div class="no-participants">

                <h3>
                    No Confirmed Participants
                </h3>

                <p>
                    There are no confirmed students for this event.
                </p>

            </div>


        <?php } ?>


    </div>


</div>
<?php if (isset($_GET["saved"]) && $_GET["saved"] == "1") { ?>
    <div class="success-message">
        Attendance saved successfully!
    </div>
<?php } ?>

</body>

</html>

<?php

$stmt->close();

$conn->close();

?>