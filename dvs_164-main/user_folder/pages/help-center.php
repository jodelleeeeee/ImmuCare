<?php

require_once __DIR__ . "/../backend/common.php";

$pageTitle = "Help Center";
$activePage = "settings";
require __DIR__ . "/../backend/page_header.php";

?>
<section class="content-card">
    <p class="page-intro">Quick answers about using your ImmuCare guardian portal.</p>
    <div class="help-list">
        <article class="help-item">
            <h2><i class="fa-solid fa-right-to-bracket"></i> How do I sign in?</h2>
            <p>Use the username and password provided by your clinic. If you do not have login credentials or they do not work, contact the clinic.</p>
        </article>
        <article class="help-item">
            <h2><i class="fa-solid fa-syringe"></i> Where can I see vaccination records?</h2>
            <p>Open Vaccination Records from the bottom navigation to see records for children linked to your guardian profile.</p>
        </article>
        <article class="help-item">
            <h2><i class="fa-solid fa-calendar-days"></i> How do I request or check an appointment?</h2>
            <p>Open Appointment to request a visit. The clinic reviews requests, and its approval or rejection appears in Notifications.</p>
        </article>
        <article class="help-item">
            <h2><i class="fa-solid fa-bell"></i> Where are clinic updates?</h2>
            <p>Notifications includes vaccination reminders, appointment updates, and announcements published by the clinic.</p>
        </article>
    </div>
    <a class="text-link" href="send-feedback.php">Still need help? Send feedback <i class="fa-solid fa-arrow-right"></i></a>
</section>
<?php require __DIR__ . "/../backend/page_footer.php"; ?>
