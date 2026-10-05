<?php

require_once __DIR__ . "/../backend/common.php";

$guardian = currentGuardian();
$records = [];
if ($guardian !== "") {
    $stmt = $mysqli->prepare("
        SELECT baby_name, age, birthday, guardian, relationship, phone_number,
               last_vaccine, status, next_vaccine, next_visit
        FROM patients
        WHERE guardian = ?
        ORDER BY baby_name ASC
    ");
    $stmt->bind_param("s", $guardian);
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$pageTitle = "Vaccination Records";
$activePage = "records";
require __DIR__ . "/../backend/page_header.php";

?>
<section class="content-card">
    <p class="page-intro">Your child's immunization information as recorded by the clinic.</p>
    <?php if ($records === [] && $guardian !== ""): ?>
        <div class="empty-state">
            <i class="fa-solid fa-clipboard-list"></i>
            <p>No vaccination records are linked to your account yet.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($records as $record): ?>
        <article class="record-card">
            <div class="record-heading">
                <div>
                    <h2><?= escapeHtml((string) $record["baby_name"]) ?></h2>
                    <p><?= escapeHtml((string) $record["relationship"]) ?> · <?= escapeHtml((string) $record["guardian"]) ?></p>
                </div>
                <span class="pill"><?= escapeHtml((string) ($record["status"] ?: "Not set")) ?></span>
            </div>
            <dl class="detail-grid">
                <div><dt>Age</dt><dd><?= escapeHtml((string) $record["age"]) ?></dd></div>
                <div><dt>Birthday</dt><dd><?= escapeHtml((string) $record["birthday"]) ?></dd></div>
                <div><dt>Phone</dt><dd><?= escapeHtml((string) $record["phone_number"]) ?></dd></div>
                <div><dt>Last vaccine</dt><dd><?= escapeHtml((string) ($record["last_vaccine"] ?: "Not recorded")) ?></dd></div>
                <div><dt>Next vaccine</dt><dd><?= escapeHtml((string) ($record["next_vaccine"] ?: "Not scheduled")) ?></dd></div>
                <div><dt>Next visit</dt><dd><?= escapeHtml((string) ($record["next_visit"] ?: "Not scheduled")) ?></dd></div>
            </dl>
        </article>
    <?php endforeach; ?>
</section>
<?php require __DIR__ . "/../backend/page_footer.php"; ?>
