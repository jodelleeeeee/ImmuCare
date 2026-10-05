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
    SELECT id, title, message, date_posted, status
    FROM announcements
    ORDER BY date_posted DESC, id DESC
");
$announcements = $result->fetch_all(MYSQLI_ASSOC);
$feedbackCount = (int) $mysqli->query("
    SELECT COUNT(*) AS total FROM user_feedback WHERE status = 'Open'
")->fetch_assoc()["total"];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Announcements | ImmuCare</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .announcement-container { padding: 20px; }
        .announcement-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; }
        .announcement-header h1 { margin: 0; color: #315f68; font-size: 26px; }
        .announcement-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .announcement-card { padding: 18px; border-radius: 14px; background: #fff; box-shadow: 0 4px 12px rgba(70,110,110,.08); }
        .announcement-card h2 { margin: 0 0 8px; color: #315f68; font-size: 18px; }
        .announcement-message { color: #637f80; font-size: 13px; line-height: 1.5; overflow-wrap: anywhere; }
        .announcement-info, .announcement-actions { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; margin-top: 12px; color: #80969a; font-size: 12px; }
        .status-badge { padding: 5px 9px; border-radius: 14px; background: #e4f8ef; color: #3f8b74; font-weight: bold; }
        .status-badge.draft { background: #fff1d8; color: #b17b34; }
        .status-badge.archived { background: #eceff1; color: #68777c; }
        .admin-link { display: inline-block; padding: 10px 14px; border-radius: 9px; background: #58af98; color: white; font-weight: bold; text-decoration: none; }
        .admin-link.secondary { background: #e4f5ff; color: #377a99; }
        .empty-message { padding: 25px; border-radius: 14px; background: white; color: #80969a; text-align: center; }
        @media (max-width: 800px) { .announcement-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <div class="logo"><img src="../images/logo.png" alt="ImmuCare" width="150"></div>
        <nav>
            <a href="../index.php">Dashboard</a>
            <a href="records.php">Records</a>
            <a href="management.php">Management</a>
            <a href="monitoring.php">Monitoring</a>
            <a href="announcements.php" class="active">Announcements</a>
            <a href="reports.php">Reports</a>
            <a href="settings.php">Settings</a>
        </nav>
    </aside>
    <main class="main">
        <header class="topbar">
            <div class="welcome-admin">Announcements</div>
            <div class="profile">🔔 👤 Profile</div>
        </header>
        <section class="announcement-container">
            <div class="announcement-header">
                <h1>Manage announcements</h1>
                <div class="announcement-actions">
                    <a class="admin-link" href="add-announcement.php">+ New announcement</a>
                    <a class="admin-link secondary" href="feedback.php">Feedback inbox<?= $feedbackCount > 0 ? " (" . $feedbackCount . ")" : "" ?></a>
                </div>
            </div>
            <?php if ($announcements === []): ?>
                <div class="empty-message">There are no announcements yet.</div>
            <?php else: ?>
                <div class="announcement-grid">
                    <?php foreach ($announcements as $announcement): ?>
                        <?php
                        $status = (string) $announcement["status"];
                        $statusLabel = in_array($status, ["Active", "Published"], true) ? "Published" : $status;
                        $statusClass = strtolower($statusLabel);
                        ?>
                        <article class="announcement-card">
                            <h2><?= escapeHtml((string) $announcement["title"]) ?></h2>
                            <p class="announcement-message"><?= nl2br(escapeHtml((string) $announcement["message"])) ?></p>
                            <div class="announcement-info">
                                <span><?= !empty($announcement["date_posted"]) ? escapeHtml(date("F j, Y", strtotime((string) $announcement["date_posted"]))) : "Date not set" ?></span>
                                <span class="status-badge <?= escapeHtml($statusClass) ?>"><?= escapeHtml($statusLabel) ?></span>
                            </div>
                            <div class="announcement-actions">
                                <a class="admin-link secondary" href="edit-announcement.php?id=<?= (int) $announcement["id"] ?>">Edit</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
