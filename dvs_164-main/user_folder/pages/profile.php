<?php

require_once __DIR__ . "/../backend/common.php";

$guardian = currentGuardian();
$records = [];
if ($guardian !== "") {
    $stmt = $mysqli->prepare("
        SELECT baby_name, birthday, relationship, phone_number
        FROM patients
        WHERE guardian = ?
        ORDER BY baby_name ASC
    ");
    $stmt->bind_param("s", $guardian);
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$pageTitle = "Profile";
$activePage = "";
require __DIR__ . "/../backend/page_header.php";

?>
<section class="content-card">
    <h2>Guardian profile</h2>
    <dl class="detail-grid">
        <div><dt>Guardian</dt><dd><?= escapeHtml($guardian !== "" ? $guardian : "Not signed in") ?></dd></div>
        <div><dt>Linked children</dt><dd><?= count($records) ?></dd></div>
    </dl>
</section>
<section class="content-card">
    <h2>Linked children</h2>
    <?php if ($records === [] && $guardian !== ""): ?>
        <div class="empty-state"><p>No children are linked to your account yet.</p></div>
    <?php endif; ?>
    <?php foreach ($records as $record): ?>
        <article class="simple-row">
            <div>
                <strong><?= escapeHtml((string) $record["baby_name"]) ?></strong>
                <span><?= escapeHtml((string) $record["relationship"]) ?></span>
            </div>
            <div class="align-right">
                <span><?= escapeHtml((string) ($record["birthday"] ?: "Birthday not recorded")) ?></span>
                <span><?= escapeHtml((string) ($record["phone_number"] ?: "Phone not recorded")) ?></span>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php require __DIR__ . "/../backend/page_footer.php"; ?>
