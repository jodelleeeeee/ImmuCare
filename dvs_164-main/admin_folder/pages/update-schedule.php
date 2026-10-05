<?php

$id = $_GET["id"] ?? null;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Update Schedule | ImmuCare</title>

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <div class="logo">

            <img
                src="../images/logo.png"
                alt="ImmuCare"
                width="150"
            >

        </div>

        <nav>

            <a href="../index.php">
                Dashboard
            </a>

            <a href="records.php">
                Records
            </a>

            <a href="management.php">
                Management
            </a>

            <a
                href="monitoring.php"
                class="active"
            >
                Monitoring
            </a>

            <a href="announcements.php">
                Announcements
            </a>

            <a href="reports.php">
                Reports
            </a>

            <a href="settings.php">
                Settings
            </a>

        </nav>

    </aside>


    <main class="main">

        <header class="topbar">

            <div class="welcome-admin">
                Update Vaccine Schedule
            </div>

            <div class="profile">
                🔔 👤 Profile
            </div>

        </header>


        <div style="
            padding:30px;
        ">

            <h1>
                Update Schedule
            </h1>

            <br>

            <p>
                Patient ID:
                <?= htmlspecialchars($id ?? "") ?>
            </p>

            <br>

            <p>
                Schedule editing will be added later.
            </p>

            <br>

            <a href="monitoring.php">
                ← Back to Monitoring
            </a>

        </div>

    </main>

</div>

</body>

</html>