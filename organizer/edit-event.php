<?php

session_start();
require_once "../database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "organizer") {
    header("Location: ../login.php");
    exit;
}

$organizer_id = $_SESSION["user_id"];

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: dashboard.php");
    exit;
}

$event_id = intval($_GET["id"]);


/* Get Event */

$sql = "SELECT * FROM events
        WHERE id = ? AND organizer_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $event_id,
    $organizer_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    header("Location: dashboard.php");
    exit;
}

$event = $result->fetch_assoc();


$message = "";
$message_type = "";


/* Update Event */

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

        $sql = "UPDATE events
                SET title = ?,
                    description = ?,
                    category = ?,
                    event_date = ?,
                    event_time = ?,
                    venue = ?,
                    capacity = ?
                WHERE id = ?
                AND organizer_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssssssiii",
            $title,
            $description,
            $category,
            $event_date,
            $event_time,
            $venue,
            $capacity,
            $event_id,
            $organizer_id
        );

        if ($stmt->execute()) {

            header("Location: dashboard.php");
            exit;

        } else {

            $message = "Failed to update event.";
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

    <title>Edit Event - Smart College Event Hub</title>

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

        <h1>Edit Event</h1>

        <p class="form-subtitle">
            Update your event information.
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
                    value="<?php echo htmlspecialchars($event["title"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Description</label>

                <textarea
                    name="description"
                    rows="5"
                ><?php echo htmlspecialchars($event["description"]); ?></textarea>

            </div>


            <div class="form-group">

                <label>Category *</label>

                <select name="category" required>

                    <?php

                    $categories = [
                        "Hackathon",
                        "Coding Competition",
                        "Workshop",
                        "Seminar",
                        "Cultural",
                        "Sports",
                        "Technical",
                        "Other"
                    ];

                    foreach ($categories as $category):

                    ?>

                        <option
                            value="<?php echo $category; ?>"
                            <?php
                            if ($event["category"] == $category) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?php echo $category; ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-row">


                <div class="form-group">

                    <label>Event Date *</label>

                    <input
                        type="date"
                        name="event_date"
                        value="<?php echo $event["event_date"]; ?>"
                        min="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Event Time *</label>

                    <input
                        type="time"
                        name="event_time"
                        value="<?php echo $event["event_time"]; ?>"
                        required
                    >

                </div>


            </div>


            <div class="form-group">

                <label>Venue *</label>

                <input
                    type="text"
                    name="venue"
                    value="<?php echo htmlspecialchars($event["venue"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Capacity *</label>

                <input
                    type="number"
                    name="capacity"
                    min="1"
                    value="<?php echo $event["capacity"]; ?>"
                    required
                >

            </div>


            <button
                type="submit"
                class="primary-btn full-btn"
            >
                Update Event
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