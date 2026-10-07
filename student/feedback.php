<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: ../login.php");
    exit;
}

include("../database.php");

$student_id = $_SESSION["user_id"];

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: my-events.php");
    exit;
}

$event_id = (int) $_GET["id"];

/* Get event and check student attendance */

$sql = "
    SELECT
        events.id,
        events.event_id,
        events.title,
        events.category,
        events.event_date,
        events.event_time,
        attendance.attendance_status
    FROM events
    INNER JOIN attendance
        ON attendance.event_id = events.id
    WHERE events.id = ?
      AND attendance.student_id = ?
      AND attendance.attendance_status = 'present'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $student_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();

    die("You can submit feedback only after attending the event.");
}

$event = $result->fetch_assoc();

$stmt->close();

/* Check whether feedback already exists */

$sql = "
    SELECT id
    FROM feedback
    WHERE event_id = ?
      AND student_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $event_id, $student_id);
$stmt->execute();

$feedback_result = $stmt->get_result();

if ($feedback_result->num_rows > 0) {
    $stmt->close();
    $conn->close();

    die("You have already submitted feedback for this event.");
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Event Feedback - Smart College Event Hub</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<nav class="navbar">

    <div class="nav-container">

        <a href="dashboard.php" class="logo">
            Smart Event Hub
        </a>

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


<div class="form-container">

    <div class="form-card">

        <h2>Event Feedback</h2>

        <p class="form-subtitle">
            Share your experience about this event.
        </p>


        <div class="event-info-box">

            <h3>
                <?php echo htmlspecialchars($event["title"]); ?>
            </h3>

            <p>
                Category:
                <?php echo htmlspecialchars($event["category"]); ?>
            </p>

            <p>
                Date:
                <?php echo htmlspecialchars($event["event_date"]); ?>
            </p>

        </div>


        <form action="submit-feedback.php"
              method="POST">


            <input type="hidden"
                   name="event_id"
                   value="<?php echo $event_id; ?>">


            <div class="form-group">

                <label>
                    Content Rating
                </label>

                <select name="content_rating" required>

                    <option value="">
                        Select Rating
                    </option>

                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Speaker Rating
                </label>

                <select name="speaker_rating" required>

                    <option value="">
                        Select Rating
                    </option>

                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Organization Rating
                </label>

                <select name="organization_rating" required>

                    <option value="">
                        Select Rating
                    </option>

                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Overall Rating
                </label>

                <select name="overall_rating" required>

                    <option value="">
                        Select Rating
                    </option>

                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Comments
                </label>

                <textarea
                    name="comments"
                    rows="5"
                    placeholder="Write your feedback here..."
                    maxlength="1000"></textarea>

            </div>


            <button type="submit"
                    class="primary-btn">
                Submit Feedback
            </button>


            <a href="my-events.php"
               class="secondary-btn">
                Back to My Events
            </a>

        </form>

    </div>

</div>

</body>

</html>