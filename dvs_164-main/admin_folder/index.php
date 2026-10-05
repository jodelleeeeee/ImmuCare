<?php

require_once "connect.php";


/* =====================================
   TOTAL REGISTERED INFANTS
===================================== */

$result = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM patients
");

$row = $result->fetch_assoc();

$totalPatients = $row["total"];


/* =====================================
   OVERDUE VACCINATIONS
===================================== */

$result = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM patients
    WHERE
        status = 'Overdue'
        OR (
            next_visit IS NOT NULL
            AND next_visit < CURDATE()
        )
");

$row = $result->fetch_assoc();

$totalOverdue = $row["total"];


/* =====================================
   NEXT VACCINE SCHEDULE
===================================== */

$result = $mysqli->query("
    SELECT
        baby_name,
        next_vaccine,
        next_visit,
        DATEDIFF(next_visit, CURDATE()) AS days_remaining
    FROM patients

    WHERE
        next_visit IS NOT NULL
        AND next_visit >= CURDATE()
        AND next_vaccine IS NOT NULL
        AND next_vaccine != ''

    ORDER BY next_visit ASC

    LIMIT 1
");

$nextSchedule = $result->fetch_assoc();


/* =====================================
   TEMPORARY VALUES
   These will become database-driven
   when vaccination_records and
   appointments tables are created.
===================================== */

$vaccinationsThisMonth = "--";

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ImmuCare Admin Dashboard</title>

    <link rel="stylesheet" href="style.css">

    <style>

        /* =====================================
           DASHBOARD EXTRA STYLES
        ===================================== */

        .welcome-admin {
            font-size: 22px;
            font-weight: 700;
            color: #315f68;
        }


        /* CLICKABLE CARDS */

        .card-link {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        .card-link .card {
            cursor: pointer;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .card-link .card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 8px 20px
                rgba(70, 110, 110, 0.13);
        }


        /* UPCOMING CARD */

        .upcoming {
            position: relative;
        }

        .upcoming-content {
            display: flex;
            align-items: center;
            gap: 20px;
            height: 100%;
        }

        .upcoming-text h2 {
            margin-bottom: 8px;
        }

        .view-link {
            font-size: 13px;
            color: #7d6874;
        }


        /* NEXT VACCINE */

        .vaccine {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .vaccine h2 {
            margin-bottom: 10px;
        }

        .vaccine-name {
            font-size: 22px;
            font-weight: 700;
            color: #39776f;
            margin-bottom: 4px;
        }

        .vaccine-patient {
            font-size: 13px;
            margin-bottom: 4px;
            color: #66858b;
        }

        .vaccine-date {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .countdown {
            font-size: 13px;
            color: #468c7b;
        }


        /* MONTHLY STATISTICS */

        .tracking {
            cursor: pointer;
        }

        .statistics-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .statistics-title span {
            font-size: 12px;
            color: #72918c;
        }

        .statistics-grid {
            flex: 1;

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 12px;

            margin-top: 18px;
        }

        .stat-box {
            background:
                rgba(255, 255, 255, 0.75);

            border-radius: 14px;

            display: flex;
            flex-direction: column;

            justify-content: center;
            align-items: center;

            text-align: center;

            padding: 15px;
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
            color: #40977f;
            margin-bottom: 7px;
        }

        .stat-label {
            font-size: 13px;
            color: #637f80;
        }


        /* USER ACTIVITY */

        .users h3 {
            margin-bottom: 4px;
        }

        .activity-heading {
            display: grid;

            grid-template-columns:
                1fr
                1fr
                1fr;

            padding: 10px 15px;

            font-size: 12px;
            font-weight: bold;

            color: #789297;
        }

        .activity-row {
            background:
                rgba(255, 255, 255, 0.9);

            display: grid;

            grid-template-columns:
                1fr
                1fr
                1fr;

            align-items: center;

            padding: 14px 15px;

            margin-top: 8px;

            border-radius: 14px;

            color: #5e7d86;

            font-size: 13px;

            box-shadow:
                0 3px 8px
                rgba(0, 0, 0, 0.03);
        }


        /* MOBILE */

        @media (max-width: 700px) {

            .statistics-grid {
                grid-template-columns: 1fr;
            }

            .activity-heading,
            .activity-row {
                grid-template-columns:
                    1fr
                    1fr;
            }

            .activity-date {
                display: none;
            }

        }

    </style>

</head>


<body>


<div class="dashboard">


    <!-- =====================================
         SIDEBAR
    ====================================== -->

    <aside class="sidebar">

        <div class="logo">

            <img
                src="./images/logo.png"
                alt="ImmuCare Logo"
                width="150"
            >

        </div>


        <nav>

            <a
                href="index.php"
                class="active"
            >
                Dashboard
            </a>

            <a href="pages/records.php">
                Records
            </a>

            <a href="pages/management.php">
                Management
            </a>

            <a href="pages/monitoring.php">
                Monitoring
            </a>

            <a href="pages/announcements.php">
                Announcements
            </a>

            <a href="pages/reports.php">
                Reports
            </a>

            <a href="pages/settings.php">
                Settings
            </a>

        </nav>

    </aside>



    <!-- =====================================
         MAIN
    ====================================== -->

    <main class="main">


        <!-- =================================
             TOP BAR
        ================================== -->

        <header class="topbar">

            <div class="welcome-admin">
                Welcome, Admin!
            </div>


            <div class="profile">

                <span>
                    🔔
                </span>

                <span>
                    👤 Profile
                </span>

            </div>

        </header>



        <!-- =================================
             DASHBOARD CARDS
        ================================== -->

        <section class="content">


            <!-- =================================
                 UPCOMING APPOINTMENTS

                 Clickable
                 Goes to Management appointments
            ================================== -->

            <a
                href="pages/management.php?section=appointments"
                class="card-link"
            >

                <div class="card upcoming">

                    <div class="upcoming-content">


                        <img
                            src="./images/appointment.png"
                            alt="Appointments"
                            width="90"
                        >


                        <div class="upcoming-text">

                            <h2>
                                Upcoming Appointments
                            </h2>

                            <p class="view-link">
                                View appointments →
                            </p>

                        </div>


                    </div>

                </div>

            </a>



            <!-- =================================
                 NEXT VACCINE SCHEDULE

                 NOT clickable
            ================================== -->

            <div class="card vaccine">

                <h2>
                    Next Vaccine Schedule
                </h2>


                <?php if ($nextSchedule): ?>


                    <div class="vaccine-name">

                        <?= htmlspecialchars(
                            $nextSchedule["next_vaccine"]
                        ) ?>

                    </div>


                    <div class="vaccine-patient">

                        Patient:

                        <?= htmlspecialchars(
                            $nextSchedule["baby_name"]
                        ) ?>

                    </div>


                    <div class="vaccine-date">

                        <?= date(
                            "F j, Y",
                            strtotime(
                                $nextSchedule["next_visit"]
                            )
                        ) ?>

                    </div>


                    <div class="countdown">

                        <?php

                        $days =
                            (int) $nextSchedule["days_remaining"];

                        if ($days === 0) {

                            echo "Scheduled today";

                        } elseif ($days === 1) {

                            echo "1 day remaining";

                        } else {

                            echo $days . " days remaining";

                        }

                        ?>

                    </div>


                <?php else: ?>


                    <p>
                        No upcoming vaccine scheduled.
                    </p>


                <?php endif; ?>


            </div>



            <!-- =================================
                 MONTHLY STATISTICS

                 Clickable → Reports
            ================================== -->

            <a
                href="pages/reports.php"
                class="card-link"
            >

                <div class="card tracking">


                    <div class="statistics-title">

                        <h3>
                            Monthly Statistics
                        </h3>

                        <span>
                            View Reports →
                        </span>

                    </div>


                    <div class="statistics-grid">


                        <!-- VACCINATIONS THIS MONTH -->

                        <div class="stat-box">

                            <div class="stat-number">

                                <?= $vaccinationsThisMonth ?>

                            </div>

                            <div class="stat-label">

                                Vaccinations
                                This Month

                            </div>

                        </div>



                        <!-- REGISTERED INFANTS -->

                        <div class="stat-box">

                            <div class="stat-number">

                                <?= $totalPatients ?>

                            </div>

                            <div class="stat-label">

                                Registered
                                Infants

                            </div>

                        </div>



                        <!-- OVERDUE -->

                        <div class="stat-box">

                            <div class="stat-number">

                                <?= $totalOverdue ?>

                            </div>

                            <div class="stat-label">

                                Overdue
                                Vaccinations

                            </div>

                        </div>


                    </div>


                </div>

            </a>



            <!-- =================================
                 RECENT USER ACTIVITY
            ================================== -->

            <div class="card users">


                <h3>
                    Recent User Activity
                </h3>


                <div class="activity-heading">

                    <span>
                        User / Parent
                    </span>

                    <span>
                        Device
                    </span>

                    <span class="activity-date">
                        Last Activity
                    </span>

                </div>



                <!-- TEMPORARY PLACEHOLDERS -->


                <div class="activity-row">

                    <span>
                        User 01
                    </span>

                    <span>
                        Mobile
                    </span>

                    <span class="activity-date">
                        --
                    </span>

                </div>


                <div class="activity-row">

                    <span>
                        User 02
                    </span>

                    <span>
                        Mobile
                    </span>

                    <span class="activity-date">
                        --
                    </span>

                </div>


                <div class="activity-row">

                    <span>
                        User 03
                    </span>

                    <span>
                        Desktop
                    </span>

                    <span class="activity-date">
                        --
                    </span>

                </div>


            </div>


        </section>


    </main>


</div>


</body>

</html>