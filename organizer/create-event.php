<?php

session_start();
require_once "../database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "organizer") {
    header("Location: ../login.php");
    exit;
}

$organizer_id = $_SESSION["user_id"];

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $event_date = $_POST["event_date"];
    $event_time = $_POST["event_time"];
    $venue = trim($_POST["venue"]);
    $capacity = intval($_POST["capacity"]);

    if (
        empty($title) ||
        empty($category) ||
        empty($event_date) ||
        empty($event_time) ||
        empty($venue) ||
        $capacity <= 0
    ) {

        $message = "Please fill all required fields correctly.";
        $message_type = "error";

    } elseif ($event_date < date("Y-m-d")) {

        $message = "Event date cannot be in the past.";
        $message_type = "error";

    } else {

        /* Generate unique Event ID */

        do {

            $event_code = "EVT" . date("YmdHis") . rand(100, 999);

            $check = $conn->prepare(
                "SELECT id FROM events WHERE event_id = ?"
            );

            $check->bind_param("s", $event_code);
            $check->execute();

            $result = $check->get_result();

        } while ($result->num_rows > 0);


        /* Insert Event */

        $sql = "INSERT INTO events
                (event_id, title, description, category, event_date,
                 event_time, venue, capacity, organizer_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssssssii",
            $event_code,
            $title,
            $description,
            $category,
            $event_date,
            $event_time,
            $venue,
            $capacity,
            $organizer_id
        );

        if ($stmt->execute()) {

            header("Location: dashboard.php");
            exit;

        } else {

            $message = "Failed to create event.";
            $message_type = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Event - Smart College Event Hub</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

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


<section class="form-section">

    <div class="form-container">

        <h1>Create New Event</h1>

        <p class="form-subtitle">
            Create an event for students.
        </p>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label>Event Title *</label>

                <input
                    type="text"
                    name="title"
                    placeholder="Enter event title"
                    required
                >

            </div>


            <div class="form-group">

                <label>Description</label>

                <textarea
                    name="description"
                    rows="5"
                    placeholder="Enter event description"
                ></textarea>

            </div>


            <div class="form-group">

                <label>Category *</label>

                <select name="category" required>

                    <option value="">Select Category</option>

                    <option value="Hackathon">
                        Hackathon
                    </option>

                    <option value="Coding Competition">
                        Coding Competition
                    </option>

                    <option value="Workshop">
                        Workshop
                    </option>

                    <option value="Seminar">
                        Seminar
                    </option>

                    <option value="Cultural">
                        Cultural
                    </option>

                    <option value="Sports">
                        Sports
                    </option>

                    <option value="Technical">
                        Technical
                    </option>

                    <option value="Other">
                        Other
                    </option>

                </select>

            </div>


            <div class="form-row">


                <div class="form-group">

                    <label>Event Date *</label>

                    <input
                        type="date"
                        name="event_date"
                        min="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Event Time *</label>

                    <input
                        type="time"
                        name="event_time"
                        required
                    >

                </div>


            </div>


            <div class="form-group">

                <label>Venue *</label>

                <input
                    type="text"
                    name="venue"
                    placeholder="Example: Seminar Hall"
                    required
                >

            </div>


            <div class="form-group">

                <label>Capacity *</label>

                <input
                    type="number"
                    name="capacity"
                    min="1"
                    placeholder="Example: 100"
                    required
                >

            </div>


            <button
                type="submit"
                class="primary-btn full-btn"
            >
                Create Event
            </button>


            <a
                href="dashboard.php"
                class="secondary-btn full-btn"
            >
                Cancel
            </a>


        </form>

    </div>

</section>

</body>

</html>