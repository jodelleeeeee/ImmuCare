<?php

require_once "../connect.php";

/* =====================================
   OVERDUE PATIENTS
===================================== */

$overdueResult = $mysqli->query("
    SELECT *,
           DATEDIFF(CURDATE(), next_visit) AS days_overdue
    FROM patients
    WHERE
        next_visit IS NOT NULL
        AND next_visit < CURDATE()
    ORDER BY next_visit ASC
");

$overduePatients = $overdueResult->fetch_all(MYSQLI_ASSOC);


/* =====================================
   UPCOMING NEXT VACCINES
===================================== */

$nextResult = $mysqli->query("
    SELECT *
    FROM patients
    WHERE
        next_visit IS NOT NULL
        AND next_visit >= CURDATE()
    ORDER BY next_visit ASC
");

$nextPatients = $nextResult->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Monitoring | ImmuCare</title>

    <link rel="stylesheet" href="../style.css">

    <style>

        .monitoring-container {
            padding: 20px;
            overflow-y: auto;
        }

        .monitoring-title {
            margin-bottom: 20px;
        }

        .monitoring-title h1 {
            font-size: 26px;
            color: #315f68;
        }

        .monitoring-section {
            background: white;

            border-radius: 16px;

            padding: 20px;

            margin-bottom: 20px;

            box-shadow:
                0 4px 12px
                rgba(70, 110, 110, 0.08);
        }

        .monitoring-section h2 {
            margin-bottom: 15px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            text-align: left;

            background: #e8f8f3;

            color: #315f68;

            padding: 13px;
        }

        td {
            padding: 13px;

            border-bottom:
                1px solid #edf2f1;

            color: #55777d;
        }

        tr:hover {
            background: #f8fcfb;
        }

        .overdue-badge {
            display: inline-block;

            background: #ffe6eb;

            color: #b85367;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }

        .upcoming-badge {
            display: inline-block;

            background: #e5f8f1;

            color: #3f8b74;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }

        .update-btn {
            display: inline-block;

            text-decoration: none;

            background: #5ab49c;

            color: white;

            padding: 8px 12px;

            border-radius: 8px;

            font-size: 13px;

            font-weight: bold;
        }

        .update-btn:hover {
            background: #459d86;
        }

        .empty-message {
            padding: 20px;
            text-align: center;
            color: #80969a;
        }

    </style>

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

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


    <!-- MAIN -->

    <main class="main">

        <header class="topbar">

            <div class="welcome-admin">
                Monitoring
            </div>

            <div class="profile">
                🔔 👤 Profile
            </div>

        </header>


        <div class="monitoring-container">

            <div class="monitoring-title">

                <h1>
                    Patient Monitoring
                </h1>

            </div>


            <!-- =================================
                 OVERDUE PATIENTS
            ================================== -->

            <div class="monitoring-section">

                <h2>
                    Overdue Vaccinations
                </h2>

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>Patient</th>

                                <th>Guardian</th>

                                <th>Phone</th>

                                <th>Last Vaccine</th>

                                <th>Next Vaccine</th>

                                <th>Scheduled Visit</th>

                                <th>Days Overdue</th>

                                <th>Status</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (count($overduePatients) > 0): ?>

                            <?php foreach ($overduePatients as $patient): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars(
                                            $patient["baby_name"] ?? ""
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $patient["guardian"] ?? ""
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $patient["phone_number"] ?? ""
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $patient["last_vaccine"] ?? ""
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $patient["next_vaccine"] ?? ""
                                        ) ?>
                                    </td>

                                    <td>

                                        <?php if (!empty($patient["next_visit"])): ?>

                                            <?= date(
                                                "M d, Y",
                                                strtotime(
                                                    $patient["next_visit"]
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            --

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?= (int) $patient["days_overdue"] ?>
                                        day(s)

                                    </td>

                                    <td>

                                        <span class="overdue-badge">

                                            Overdue

                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="8">

                                    <div class="empty-message">

                                        No overdue patients.

                                    </div>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- =================================
                 NEXT VACCINE SCHEDULE
            ================================== -->

            <div class="monitoring-section">

                <h2>
                    Next Vaccine Schedule
                </h2>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>Patient</th>

                                <th>Guardian</th>

                                <th>Next Vaccine</th>

                                <th>Next Visit</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (count($nextPatients) > 0): ?>

                            <?php foreach ($nextPatients as $patient): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars(
                                            $patient["baby_name"] ?? ""
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $patient["guardian"] ?? ""
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $patient["next_vaccine"] ?? ""
                                        ) ?>
                                    </td>

                                    <td>

                                        <?= date(
                                            "M d, Y",
                                            strtotime(
                                                $patient["next_visit"]
                                            )
                                        ) ?>

                                    </td>

                                    <td>

                                        <span class="upcoming-badge">

                                            Scheduled

                                        </span>

                                    </td>

                                    <td>

                                        <a
                                            href="update-schedule.php?id=<?= $patient["id"] ?>"
                                            class="update-btn"
                                        >
                                            Update
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="6">

                                    <div class="empty-message">

                                        No upcoming vaccine schedules.

                                    </div>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </main>

</div>

</body>

</html>