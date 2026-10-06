<?php
require_once "database.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Smart College Event Hub</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>

<!-- ================= NAVBAR ================= -->

<header class="navbar">

    <div class="logo">
        Smart Event Hub
    </div>

    <nav>

        <a href="index.php">Home</a>
        <a href="#events">Events</a>
        <a href="#categories">Categories</a>
        <a href="#how-it-works">How It Works</a>

        <a href="login.php" class="login-btn">
            Login
        </a>

        <a href="register.php" class="register-btn">
            Register
        </a>

    </nav>

</header>


<!-- ================= HERO SECTION ================= -->

<section class="hero">

    <div class="hero-content">

        <p class="hero-small">
            COLLEGE EVENT MANAGEMENT PLATFORM
        </p>

        <h1>
            Smart College<br>
            Event Hub
        </h1>

        <p class="hero-description">

            Discover college events, register easily,
            participate in exciting activities and
            share your experience.

        </p>

        <div class="hero-buttons">

            <a href="#events" class="primary-btn">
                Explore Events
            </a>

            <a href="register.php" class="secondary-btn">
                Join Now
            </a>

        </div>

    </div>

</section>


<!-- ================= CATEGORIES ================= -->

<section id="categories" class="section">

    <div class="section-heading">

        <p>EXPLORE</p>

        <h2>
            Event Categories
        </h2>

        <span>
            Find events that match your interests.
        </span>

    </div>


    <div class="category-container">

        <div class="category-card">
            <div class="category-icon">💻</div>
            <h3>Hackathon</h3>
            <p>Build innovative solutions.</p>
        </div>


        <div class="category-card">
            <div class="category-icon">👨‍💻</div>
            <h3>Coding</h3>
            <p>Improve your programming skills.</p>
        </div>


        <div class="category-card">
            <div class="category-icon">🎓</div>
            <h3>Workshop</h3>
            <p>Learn from experts.</p>
        </div>


        <div class="category-card">
            <div class="category-icon">🎤</div>
            <h3>Seminar</h3>
            <p>Gain knowledge and insights.</p>
        </div>


        <div class="category-card">
            <div class="category-icon">🏆</div>
            <h3>Sports</h3>
            <p>Participate and compete.</p>
        </div>


        <div class="category-card">
            <div class="category-icon">🎭</div>
            <h3>Cultural</h3>
            <p>Show your creativity.</p>
        </div>

    </div>

</section>


<!-- ================= EVENTS ================= -->

<section id="events" class="section events-section">

    <div class="section-heading">

        <p>DISCOVER</p>

        <h2>
            Upcoming Events
        </h2>

        <span>
            Explore exciting events happening in your college.
        </span>

    </div>


    <div class="event-message">

        <div class="event-message-icon">
            📅
        </div>

        <h3>
            Events Coming Soon
        </h3>

        <p>
            Upcoming college events will appear here.
            Register and participate in exciting activities.
        </p>

    </div>

</section>


<!-- ================= HOW IT WORKS ================= -->

<section id="how-it-works" class="section">

    <div class="section-heading">

        <p>SIMPLE PROCESS</p>

        <h2>
            How It Works
        </h2>

    </div>


    <div class="steps-container">

        <div class="step-card">

            <div class="step-number">
                01
            </div>

            <h3>
                Discover
            </h3>

            <p>
                Browse upcoming college events
                and find events you like.
            </p>

        </div>


        <div class="step-card">

            <div class="step-number">
                02
            </div>

            <h3>
                Register
            </h3>

            <p>
                Register for your selected event
                with a few simple clicks.
            </p>

        </div>


        <div class="step-card">

            <div class="step-number">
                03
            </div>

            <h3>
                Participate
            </h3>

            <p>
                Attend the event and
                mark your participation.
            </p>

        </div>


        <div class="step-card">

            <div class="step-number">
                04
            </div>

            <h3>
                Give Feedback
            </h3>

            <p>
                Share your experience and
                rate the event.
            </p>

        </div>

    </div>

</section>


<!-- ================= STATISTICS ================= -->

<section class="statistics">

    <div class="stat">

        <h2>100+</h2>

        <p>
            Students
        </p>

    </div>


    <div class="stat">

        <h2>25+</h2>

        <p>
            Events
        </p>

    </div>


    <div class="stat">

        <h2>10+</h2>

        <p>
            Organizers
        </p>

    </div>


    <div class="stat">

        <h2>500+</h2>

        <p>
            Registrations
        </p>

    </div>

</section>


<!-- ================= FOOTER ================= -->

<footer>

    <div class="footer-content">

        <h2>
            Smart College Event Hub
        </h2>

        <p>
            Discover • Register • Participate • Experience
        </p>

    </div>


    <div class="footer-bottom">

        <p>
            © 2026 Smart College Event Hub.
            All Rights Reserved.
        </p>

    </div>

</footer>


</body>

</html>