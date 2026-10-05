<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Administrator access is required.");
}

require_once __DIR__ . "/../../user_folder/backend/common.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
}
if (!$id) {
    http_response_code(400);
    exit("A valid announcement is required.");
}

$stmt = $mysqli->prepare("SELECT title, message, status FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$announcement = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$announcement) {
    http_response_code(404);
    exit("Announcement not found.");
}

$statuses = ["Draft", "Active", "Archived"];
$error = "";
$title = (string) $announcement["title"];
$message = (string) $announcement["message"];
$status = in_array($announcement["status"], $statuses, true) ? (string) $announcement["status"] : "Archived";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"] ?? "";
    $message = $_POST["message"] ?? "";
    $status = $_POST["status"] ?? "";

    if (!validateCsrfToken()) {
        http_response_code(400);
        $error = "This form expired. Reload the page and try again.";
    } elseif (
        !is_string($title)
        || !is_string($message)
        || !is_string($status)
        || trim($title) === ""
        || strlen(trim($title)) > 255
        || trim($message) === ""
        || strlen(trim($message)) > 10000
        || !in_array($status, $statuses, true)
    ) {
        $error = "Enter a title, message, and valid status. The title is limited to 255 characters and the message to 10,000.";
    } else {
        $title = trim($title);
        $message = trim($message);
        $stmt = $mysqli->prepare("UPDATE announcements SET title = ?, message = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $message, $status, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: announcements.php");
        exit;
    }
}

$csrfToken = ensureCsrfToken();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Announcement | ImmuCare</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .form-container { width: min(100% - 30px, 700px); margin: 35px auto; padding: 26px; border-radius: 18px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,.05); }
        .form-container h1 { margin: 0 0 20px; color: #315f68; }
        .form-container form { display: grid; gap: 14px; }
        .form-container label { display: grid; gap: 6px; color: #416f72; font-weight: bold; }
        .form-container input, .form-container textarea, .form-container select { width: 100%; padding: 11px; border: 1px solid #dcebea; border-radius: 9px; font: inherit; }
        .form-actions { display: flex; align-items: center; gap: 12px; }
        .form-actions button { padding: 12px 18px; border: 0; border-radius: 9px; background: #58af98; color: white; font-weight: bold; cursor: pointer; }
        .form-actions a { color: #677f82; }
        .form-error { padding: 11px; border-radius: 9px; background: #fff0f4; color: #9b5268; }
    </style>
</head>
<body>
<main class="form-container">
    <h1>Edit announcement</h1>
    <?php if ($error !== ""): ?><div class="form-error" role="alert"><?= escapeHtml($error) ?></div><?php endif; ?>
    <form method="post" action="edit-announcement.php">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
        <input type="hidden" name="id" value="<?= (int) $id ?>">
        <label>Title<input name="title" maxlength="255" value="<?= escapeHtml(is_string($title) ? $title : "") ?>" required></label>
        <label>Message<textarea name="message" rows="6" maxlength="10000" required><?= escapeHtml(is_string($message) ? $message : "") ?></textarea></label>
        <label>Status
            <select name="status" required>
                <?php foreach ($statuses as $option): ?>
                    <option value="<?= escapeHtml($option) ?>" <?= $status === $option ? "selected" : "" ?>><?= escapeHtml($option === "Active" ? "Published" : $option) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="form-actions"><button type="submit">Save changes</button><a href="announcements.php">Cancel</a></div>
    </form>
</main>
</body>
</html>
