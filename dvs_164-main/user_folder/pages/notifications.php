<?php

require_once __DIR__ . "/../backend/common.php";

$guardian = currentGuardian();
$notices = [];
if ($guardian !== "") {
    $announcementResult = $mysqli->query("
        SELECT title, message, date_posted
        FROM announcements
        WHERE status IN ('Active', 'Published')
        ORDER BY date_posted DESC, id DESC
        LIMIT 20
    ");
    foreach ($announcementResult->fetch_all(MYSQLI_ASSOC) as $announcement) {
        $notices[] = [
            "title" => "Clinic announcement: " . (string) $announcement["title"],
            "text" => (string) $announcement["message"]
                . (!empty($announcement["date_posted"])
                    ? " · " . date("F j, Y", strtotime((string) $announcement["date_posted"]))
                    : ""),
            "class" => "notice-info",
        ];
    }

    $stmt = $mysqli->prepare("
        SELECT baby_name, next_vaccine, next_visit, status
        FROM patients
        WHERE guardian = ?
          AND (
              status = 'Overdue'
              OR (next_visit IS NOT NULL AND next_visit < CURDATE())
              OR (next_visit IS NOT NULL AND next_visit >= CURDATE())
          )
        ORDER BY next_visit ASC
    ");
    $stmt->bind_param("s", $guardian);
    $stmt->execute();
    $patientNotices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($patientNotices as $notice) {
        $overdue = strtolower((string) $notice["status"]) === "overdue"
            || (!empty($notice["next_visit"]) && $notice["next_visit"] < date("Y-m-d"));
        $notices[] = [
            "title" => $overdue ? "Vaccination may be overdue" : "Upcoming clinic visit",
            "text" => (string) $notice["baby_name"]
                . (!empty($notice["next_vaccine"]) ? " · " . $notice["next_vaccine"] : "")
                . (!empty($notice["next_visit"]) ? " · " . date("F j, Y", strtotime((string) $notice["next_visit"])) : ""),
            "class" => $overdue ? "notice-error" : "notice-info",
        ];
    }

    $stmt = $mysqli->prepare("
        SELECT ar.status, ar.appointment_date, ar.appointment_time, ar.admin_note, p.baby_name
        FROM appointment_requests ar
        INNER JOIN patients p ON p.id = ar.patient_id
        WHERE ar.guardian = ? AND p.guardian = ? AND ar.status IN ('Approved', 'Rejected')
        ORDER BY ar.updated_at DESC
        LIMIT 20
    ");
    $stmt->bind_param("ss", $guardian, $guardian);
    $stmt->execute();
    $requestNotices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($requestNotices as $notice) {
        $approved = $notice["status"] === "Approved";
        $notices[] = [
            "title" => "Appointment request " . strtolower((string) $notice["status"]),
            "text" => (string) $notice["baby_name"] . " · "
                . date("F j, Y", strtotime((string) $notice["appointment_date"])) . " · "
                . date("g:i A", strtotime((string) $notice["appointment_time"]))
                . ($notice["admin_note"] !== "" ? " · " . $notice["admin_note"] : ""),
            "class" => $approved ? "notice-info" : "notice-error",
        ];
    }

    $stmt = $mysqli->prepare("
        SELECT category, status, created_at
        FROM user_feedback
        WHERE guardian = ?
        ORDER BY updated_at DESC
        LIMIT 10
    ");
    $stmt->bind_param("s", $guardian);
    $stmt->execute();
    $feedbackNotices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($feedbackNotices as $notice) {
        $resolved = $notice["status"] === "Resolved";
        $notices[] = [
            "title" => "Your " . strtolower((string) $notice["category"]) . " is " . strtolower((string) $notice["status"]),
            "text" => "Sent " . date("F j, Y", strtotime((string) $notice["created_at"]))
                . ($resolved ? " · The clinic has marked it resolved." : " · The clinic team will review it."),
            "class" => $resolved ? "notice-info" : "notice",
        ];
    }
}

$pageTitle = "Notifications";
$activePage = "notifications";
require __DIR__ . "/../backend/page_header.php";

?>
<section class="content-card">
    <p class="page-intro">Vaccination reminders, clinic announcements, feedback updates, and appointment information.</p>
    <?php if ($notices === [] && $guardian !== ""): ?>
        <div class="empty-state">
            <i class="fa-solid fa-heart"></i>
            <p>You’re all caught up. There are no new reminders.</p>
        </div>
    <?php endif; ?>
    <div class="notice-list">
        <?php foreach ($notices as $notice): ?>
            <article class="notice <?= escapeHtml($notice["class"]) ?>">
                <h2><?= escapeHtml($notice["title"]) ?></h2>
                <p><?= escapeHtml($notice["text"]) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
    <a class="text-link" href="help-center.php">Visit the Help Center <i class="fa-solid fa-arrow-right"></i></a>
    <a class="text-link" href="appointments.php">View appointment requests <i class="fa-solid fa-arrow-right"></i></a>
</section>
<?php require __DIR__ . "/../backend/page_footer.php"; ?>
