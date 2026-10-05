<?php

$pageTitle = $pageTitle ?? "ImmuCare";
$activePage = $activePage ?? "";
$guardian = currentGuardian();
$flash = takeUserFlash();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapeHtml($pageTitle) ?> | ImmuCare</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app inner-app">
    <header class="inner-header">
        <a class="inner-brand" href="../index.php">
            <img src="../images/logo.png" alt="ImmuCare">
            <span>Infant Immunization Ledger</span>
        </a>
        <div class="inner-header-actions">
            <a class="inner-profile" href="profile.php">
                <span class="inner-avatar">👩🏻</span>
                <span><?= escapeHtml($guardian !== "" ? $guardian : "Profile") ?></span>
            </a>
            <?php if ($guardian !== ""): ?>
                <a class="sign-out-link" href="../../user_login/logout.php">Sign out</a>
            <?php else: ?>
                <a class="sign-out-link" href="../../user_login/">Sign in</a>
            <?php endif; ?>
            <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle dark theme">
                <i class="fa-solid fa-circle-half-stroke"></i>
            </button>
        </div>
    </header>

    <main class="inner-page">
        <div class="inner-page-heading">
            <a class="back-home" href="../index.php" aria-label="Back to home">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1><?= escapeHtml($pageTitle) ?></h1>
        </div>

        <?php if ($flash): ?>
            <div class="notice notice-<?= escapeHtml((string) ($flash["type"] ?? "success")) ?>" role="status">
                <?= escapeHtml((string) ($flash["message"] ?? "")) ?>
            </div>
        <?php endif; ?>

        <?php if ($guardian === ""): ?>
            <div class="notice notice-error" role="alert">
                Sign in with the guardian name and phone number registered with the clinic to view your information.
                <a href="../../user_login/">Sign in</a>
            </div>
        <?php endif; ?>
