<?php
require_once __DIR__ . "/backend/common.php";

$guardian = isset($_SESSION["guardian"]) && is_string($_SESSION["guardian"])
    ? trim($_SESSION["guardian"])
    : "";
$avatarFilename = guardianAvatarFilename($guardian);
$patients = [];
$vaccinationStatus = "No records";
$remindersDue = 0;
$upcomingAppointments = [];
$appointmentDates = [];
$appointmentRequests = [];

if ($guardian !== "") {
    $stmt = $mysqli->prepare("
        SELECT id, baby_name, status, next_vaccine, next_visit
        FROM patients
        WHERE guardian = ?
        ORDER BY
            (next_visit IS NULL) ASC,
            next_visit ASC,
            baby_name ASC
    ");
    $stmt->bind_param("s", $guardian);
    $stmt->execute();
    $patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $requestStmt = $mysqli->prepare("
        SELECT p.baby_name, ar.appointment_date AS next_visit,
               ar.appointment_time, ar.status
        FROM appointment_requests ar
        INNER JOIN patients p ON p.id = ar.patient_id
        WHERE ar.guardian = ?
          AND p.guardian = ?
          AND ar.status IN ('Pending', 'Approved')
          AND ar.appointment_date >= CURDATE()
        ORDER BY ar.appointment_date ASC, ar.appointment_time ASC
    ");
    $requestStmt->bind_param("ss", $guardian, $guardian);
    $requestStmt->execute();
    $appointmentRequests = $requestStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $requestStmt->close();

    $hasOverdue = false;
    $hasPending = false;
    $hasUpToDate = false;
    $today = date("Y-m-d");

    foreach ($patients as $patient) {
        $status = strtolower(trim((string) ($patient["status"] ?? "")));
        $nextVisit = $patient["next_visit"] ?? "";

        if ($status === "overdue" || ($nextVisit !== "" && $nextVisit < $today)) {
            $hasOverdue = true;
            $remindersDue++;
        } elseif ($status === "pending") {
            $hasPending = true;
        } elseif ($status === "up to date") {
            $hasUpToDate = true;
        }

        if ($nextVisit !== "" && $nextVisit >= $today) {
            $upcomingAppointments[] = $patient;
            $appointmentDates[$nextVisit][] = $patient["baby_name"];
        }
    }

    foreach ($appointmentRequests as $appointmentRequest) {
        $upcomingAppointments[] = $appointmentRequest;
        $appointmentDates[$appointmentRequest["next_visit"]][] = $appointmentRequest["baby_name"];
    }

    if ($hasOverdue) {
        $vaccinationStatus = "Overdue";
    } elseif ($hasPending) {
        $vaccinationStatus = "Pending";
    } elseif ($hasUpToDate) {
        $vaccinationStatus = "Up to Date";
    } elseif ($patients !== []) {
        $vaccinationStatus = "Completed";
    }
}
$vaccinationStatusClass = $vaccinationStatus === "Overdue"
    ? "pink-label"
    : ($vaccinationStatus === "Pending" ? "blue-label" : "green-label");

$requestedMonth = $_GET["month"] ?? date("Y-m");
if (
    !is_string($requestedMonth)
    || !preg_match('/^(\d{4})-(\d{2})$/', $requestedMonth, $monthParts)
    || !checkdate((int) $monthParts[2], 1, (int) $monthParts[1])
) {
    $requestedMonth = date("Y-m");
}

$calendarMonth = new DateTimeImmutable($requestedMonth . "-01");
$previousMonth = $calendarMonth->modify("-1 month")->format("Y-m");
$nextMonth = $calendarMonth->modify("+1 month")->format("Y-m");
$daysInMonth = (int) $calendarMonth->format("t");
$firstWeekday = (int) $calendarMonth->format("w");
$today = date("Y-m-d");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ImmuCare</title>

    <link rel="stylesheet" href="style.css">

    <!-- Icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="app">

    <!-- HEADER -->
    <section class="hero">

        <div class="hero-left">

            <div class="brand">

    <img src="images/logo.png" alt="ImmuCare Logo" class="header-logo">

    <p class="ledger-text">Infant Immunization Ledger</p>

</div>


            <div class="welcome">
                <h2>Welcome,<br><?= htmlspecialchars($guardian !== "" ? $guardian : "there", ENT_QUOTES, "UTF-8") ?>!</h2>
                <p>Healthy babies, brighter tomorrows.</p>
            </div>

        </div>


        <div class="hero-right">

            <a class="profile profile-link" href="pages/profile.php">
                <?php if ($avatarFilename !== ""): ?>
                    <img class="avatar" src="uploads/avatars/<?= htmlspecialchars($avatarFilename, ENT_QUOTES, "UTF-8") ?>" alt="">
                <?php else: ?>
                    <div class="avatar" aria-hidden="true">👩🏻</div>
                <?php endif; ?>

                <div class="profile-text">
                    Profile
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </a>


            <div class="theme-switch" aria-label="Theme">
                <button class="sun" type="button" data-theme-choice="light" aria-label="Light theme">
                    <i class="fa-solid fa-sun"></i>
                </button>

                <button class="moon" type="button" data-theme-choice="dark" aria-label="Dark theme">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>

        </div>



    </section>

    <?php if ($guardian === ""): ?>
        <p class="dashboard-message" role="status">
            Sign in with the username and password provided by your clinic to view your records.
            <a href="user_login/">Sign in</a>
        </p>
    <?php elseif ($patients === []): ?>
        <p class="dashboard-message" role="status">
            No patient records are linked to <?= htmlspecialchars($guardian, ENT_QUOTES, "UTF-8") ?> yet.
        </p>
    <?php endif; ?>



    <!-- CALENDAR -->
    <section class="calendar-section">

        <div class="calendar-header">

            <div class="calendar-title">
                <i class="fa-solid fa-calendar-days"></i>
                <h2><?= htmlspecialchars($calendarMonth->format("F Y"), ENT_QUOTES, "UTF-8") ?></h2>
            </div>

            <div class="calendar-buttons">

                <a href="?month=<?= htmlspecialchars($previousMonth, ENT_QUOTES, "UTF-8") ?>" aria-label="Previous month">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>

                <a href="?month=<?= htmlspecialchars($nextMonth, ENT_QUOTES, "UTF-8") ?>" aria-label="Next month">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>

            </div>

        </div>



        <!-- WEEK DAYS -->
        <div class="weekdays">

            <div class="pink">Sun</div>
            <div class="blue">Mon</div>
            <div class="green">Tue</div>
            <div class="pink">Wed</div>
            <div class="blue">Thu</div>
            <div class="green">Fri</div>
            <div class="pink">Sat</div>

        </div>



        <!-- CALENDAR DAYS -->
        <div class="calendar-grid">

            <?php for ($blank = 0; $blank < $firstWeekday; $blank++): ?>
                <div></div>
            <?php endfor; ?>

            <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                <?php
                $calendarDate = $calendarMonth->format("Y-m-") . str_pad((string) $day, 2, "0", STR_PAD_LEFT);
                $dayClasses = [];
                if ($calendarDate === $today) {
                    $dayClasses[] = "active-day";
                }
                if (isset($appointmentDates[$calendarDate])) {
                    $dayClasses[] = "appointment-day";
                }
                $dayTitle = isset($appointmentDates[$calendarDate])
                    ? "Appointment: " . implode(", ", $appointmentDates[$calendarDate])
                    : "";
                ?>
                <div class="<?= implode(" ", $dayClasses) ?>"<?= $dayTitle !== "" ? ' title="' . htmlspecialchars($dayTitle, ENT_QUOTES, "UTF-8") . '"' : "" ?>><?= $day ?></div>
            <?php endfor; ?>

            <?php
            $calendarCellCount = $firstWeekday + $daysInMonth;
            $trailingBlanks = (7 - ($calendarCellCount % 7)) % 7;
            for ($blank = 0; $blank < $trailingBlanks; $blank++):
            ?>
                <div></div>
            <?php endfor; ?>

        </div>







        <div class="status-grid">

            <!-- STATUS -->
            <a class="status-card green-card" href="pages/records.php">

                <div class="card-icon green-icon">
                    <i class="fa-solid fa-syringe"></i>
                </div>

                <div class="card-text">

                    <h3>Vaccination<br>Status</h3>

                    <span class="status-label <?= $vaccinationStatusClass ?>">
                        <?= htmlspecialchars($vaccinationStatus, ENT_QUOTES, "UTF-8") ?>
                    </span>

                </div>

                <i class="fa-solid fa-chevron-right arrow"></i>

            </a>



            <!-- REMINDERS -->
            <a class="status-card pink-card" href="pages/notifications.php">

                <div class="card-icon pink-icon">
                    <i class="fa-solid fa-exclamation"></i>
                </div>

                <div class="card-text">

                    <h3>Reminders</h3>

                    <span class="status-label pink-label">
                        <?= $remindersDue ?> Due
                    </span>

                </div>

                <i class="fa-solid fa-chevron-right arrow"></i>

            </a>



         
           <!-- APPOINTMENT -->
<a class="status-card blue-card appointment-wide" href="pages/appointments.php">

    <div class="card-icon blue-icon">
        <i class="fa-solid fa-calendar-days"></i>
    </div>

    <div class="card-text">

        <h3>Appointment</h3>

        <span class="status-label blue-label">
            <?= count($upcomingAppointments) > 0 ? count($upcomingAppointments) . " Upcoming" : "Request a visit" ?>
        </span>
        <?php if ($upcomingAppointments !== []): ?>
            <?php $appointment = $upcomingAppointments[0]; ?>
            <small>
                <?= htmlspecialchars((string) $appointment["baby_name"], ENT_QUOTES, "UTF-8") ?>
                · <?= date("M j, Y", strtotime((string) $appointment["next_visit"])) ?>
            </small>
        <?php endif; ?>

    </div>

    <i class="fa-solid fa-chevron-right arrow"></i>

</a>



    </section>



    <!-- BOTTOM NAV -->
    <nav class="bottom-nav">

        <a href="pages/notifications.php">
            <i class="fa-solid fa-heart pink-nav"></i>
            <span>Notifications</span>
        </a>

        <a href="pages/records.php">
            <i class="fa-solid fa-clipboard-list pink-nav"></i>
            <span>Vaccination<br>Records</span>
        </a>

        <a href="index.php" class="active-nav">
            <div class="active-nav-icon">
                <i class="fa-solid fa-house"></i>
            </div>
            <span>Home</span>
        </a>

        <a href="pages/appointments.php">
            <i class="fa-solid fa-circle-plus blue-nav"></i>
            <span>Appointment</span>
        </a>

        <a href="pages/settings.php">
            <i class="fa-solid fa-grip purple-nav"></i>
            <span>Settings</span>
        </a>

    </nav>

</div>

<script src="app.js"></script>
</body>
</html>