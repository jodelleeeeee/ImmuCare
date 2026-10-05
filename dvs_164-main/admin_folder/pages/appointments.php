<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Administrator access is required.");
}

require_once __DIR__ . "/../../user_folder/backend/common.php";

$result = $mysqli->query("
    SELECT ar.id, ar.guardian, ar.appointment_date, ar.appointment_time,
           ar.notes, ar.status, ar.admin_note, ar.created_at, p.baby_name,
           p.phone_number
    FROM appointment_requests ar
    INNER JOIN patients p ON p.id = ar.patient_id
    ORDER BY
        CASE ar.status WHEN 'Pending' THEN 0 WHEN 'Approved' THEN 1 ELSE 2 END,
        ar.appointment_date ASC,
        ar.appointment_time ASC
");
$requests = $result->fetch_all(MYSQLI_ASSOC);
$flash = takeUserFlash();
$csrfToken = ensureCsrfToken();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments | ImmuCare</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .appointments-container { padding: 20px; overflow-y: auto; }
        .appointments-container h1 { margin: 0 0 8px; color: #315f68; font-size: 25px; }
        .appointments-intro { margin: 0 0 18px; color: #71868a; }
        .request-admin-card { margin: 12px 0; padding: 18px; border-radius: 14px; background: #fff; box-shadow: 0 4px 12px rgba(70,110,110,.08); }
        .request-admin-heading { display: flex; justify-content: space-between; gap: 12px; }
        .request-admin-heading h2 { margin: 0 0 5px; color: #315f68; font-size: 17px; }
        .request-admin-heading p, .request-admin-card p { color: #637f80; font-size: 13px; }
        .request-status { align-self: flex-start; padding: 6px 10px; border-radius: 14px; background: #fff2dc; color: #9a6e28; font-size: 12px; font-weight: bold; }
        .request-status.approved { background: #e5f6ee; color: #438d72; }
        .request-status.rejected { background: #fff0f4; color: #c15a78; }
        .admin-request-form { display: grid; gap: 10px; margin-top: 14px; }
        .admin-request-form label { display: grid; gap: 5px; color: #416f72; font-size: 12px; font-weight: bold; }
        .admin-request-form input, .admin-request-form textarea { padding: 9px; border: 1px solid #d9e8e4; border-radius: 8px; font: inherit; }
        .admin-request-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .admin-request-actions button { padding: 9px 13px; border: 0; border-radius: 8px; color: white; font-weight: bold; cursor: pointer; }
        .approve-action { background: #55ad90; }
        .reject-action { background: #dc6d8d; }
        .reschedule-action { background: #5a9fca; }
        .admin-flash { margin-bottom: 14px; padding: 12px; border-radius: 9px; background: #e9f8ef; color: #39745d; }
        .admin-flash.error { background: #fff0f4; color: #9b5268; }
    </style>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo"><img src="../images/logo.png" alt="ImmuCare" width="150"></div>
        <nav>
            <a href="../index.php">Dashboard</a>
            <a href="records.php">Records</a>
            <a href="management.php" class="active">Management</a>
            <a href="monitoring.php">Monitoring</a>
            <a href="announcements.php">Announcements</a>
            <a href="reports.php">Reports</a>
            <a href="settings.php">Settings</a>
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="welcome-admin">Appointment Management</div>
            <div class="profile">🔔 👤 Profile</div>
        </header>
        <section class="appointments-container">
            <h1>Appointment requests</h1>
            <p class="appointments-intro">Review guardian requests. Approved or rejected requests are visible in the user's notifications.</p>
            <?php if ($flash): ?>
                <div class="admin-flash <?= ($flash["type"] ?? "") === "error" ? "error" : "" ?>" role="status">
                    <?= escapeHtml((string) ($flash["message"] ?? "")) ?>
                </div>
            <?php endif; ?>

            <?php if ($requests === []): ?>
                <div class="request-admin-card">No appointment requests have been submitted.</div>
            <?php endif; ?>

            <?php foreach ($requests as $request): ?>
                <article class="request-admin-card">
                    <div class="request-admin-heading">
                        <div>
                            <h2><?= escapeHtml((string) $request["baby_name"]) ?></h2>
                            <p>Guardian: <?= escapeHtml((string) $request["guardian"]) ?>
                                · Phone: <?= escapeHtml((string) ($request["phone_number"] ?? "")) ?></p>
                            <p><?= date("F j, Y", strtotime((string) $request["appointment_date"])) ?>
                                · <?= date("g:i A", strtotime((string) $request["appointment_time"])) ?></p>
                        </div>
                        <span class="request-status <?= strtolower((string) $request["status"]) ?>">
                            <?= escapeHtml((string) $request["status"]) ?>
                        </span>
                    </div>
                    <?php if ($request["notes"] !== ""): ?>
                        <p><strong>Guardian note:</strong> <?= escapeHtml((string) $request["notes"]) ?></p>
                    <?php endif; ?>
                    <?php if ($request["admin_note"] !== ""): ?>
                        <p><strong>Clinic note:</strong> <?= escapeHtml((string) $request["admin_note"]) ?></p>
                    <?php endif; ?>

                    <?php if ($request["status"] === "Pending"): ?>
                        <form class="admin-request-form" action="../../user_folder/backend/update_appointment.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
                            <input type="hidden" name="request_id" value="<?= (int) $request["id"] ?>">
                            <div class="admin-request-actions">
                                <button class="approve-action" type="submit" name="action" value="approve">Approve</button>
                                <button class="reject-action" type="submit" name="action" value="reject">Reject</button>
                            </div>
                            <div class="admin-request-actions">
                                <label>Change date<input type="date" name="appointment_date" min="<?= date("Y-m-d") ?>" value="<?= escapeHtml((string) $request["appointment_date"]) ?>"></label>
                                <label>Change time<input type="time" name="appointment_time" value="<?= escapeHtml(substr((string) $request["appointment_time"], 0, 5)) ?>"></label>
                                <button class="reschedule-action" type="submit" name="action" value="reschedule">Reschedule</button>
                            </div>
                            <label>Message to guardian<textarea name="admin_note" maxlength="500" rows="2"><?= escapeHtml((string) $request["admin_note"]) ?></textarea></label>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
            <p><a href="management.php">← Back to Management</a></p>
        </section>
    </main>
</div>
</body>
</html>
