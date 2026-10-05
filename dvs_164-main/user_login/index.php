<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentGuardian = isset($_SESSION["guardian"]) && is_string($_SESSION["guardian"])
    ? trim($_SESSION["guardian"])
    : "";

if ($currentGuardian !== "") {
    header("Location: ../user_folder/index.php");
    exit;
}

if (!isset($_SESSION["login_csrf_token"]) || !is_string($_SESSION["login_csrf_token"])) {
    $_SESSION["login_csrf_token"] = bin2hex(random_bytes(32));
}

$error = "";
$guardianName = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedToken = $_POST["csrf_token"] ?? "";
    $guardianName = $_POST["guardian"] ?? "";
    $phoneNumber = $_POST["phone_number"] ?? "";

    if (
        !is_string($submittedToken)
        || !hash_equals($_SESSION["login_csrf_token"], $submittedToken)
    ) {
        http_response_code(400);
        $error = "This sign-in form expired. Refresh the page and try again.";
    } elseif (
        !is_string($guardianName)
        || !is_string($phoneNumber)
        || trim($guardianName) === ""
        || trim($phoneNumber) === ""
    ) {
        $error = "Enter the guardian name and phone number registered with the clinic.";
    } else {
        $guardianName = trim($guardianName);
        $enteredDigits = preg_replace('/\D+/', '', $phoneNumber);
        $matchedGuardian = null;

        if (is_string($enteredDigits) && $enteredDigits !== "") {
            require_once __DIR__ . "/../user_folder/connect.php";

            $stmt = $mysqli->prepare("
                SELECT guardian, phone_number
                FROM patients
                WHERE guardian = ?
            ");
            $stmt->bind_param("s", $guardianName);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($record = $result->fetch_assoc()) {
                $storedDigits = preg_replace('/\D+/', '', (string) $record["phone_number"]);
                if (is_string($storedDigits) && hash_equals($storedDigits, $enteredDigits)) {
                    $matchedGuardian = (string) $record["guardian"];
                    break;
                }
            }

            $stmt->close();
        }

        if ($matchedGuardian !== null) {
            session_regenerate_id(true);
            $_SESSION["guardian"] = $matchedGuardian;
            unset($_SESSION["login_csrf_token"]);
            header("Location: ../user_folder/index.php");
            exit;
        }

        $error = "We couldn't match those details to a clinic record. Check the information or contact the clinic.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e8f7f6">
    <title>Guardian Sign In | ImmuCare</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="login-shell">
    <section class="login-card" aria-labelledby="login-title">
        <a class="brand" href="../user_folder/index.php" aria-label="ImmuCare home">
            <img src="../user_folder/images/logo.png" alt="">
            <span>Infant Immunization Ledger</span>
        </a>

        <div class="welcome-icon" aria-hidden="true">👶🏻</div>
        <p class="eyebrow">GUARDIAN PORTAL</p>
        <h1 id="login-title">Welcome to ImmuCare</h1>
        <p class="intro">Sign in to view your child’s vaccination records and appointments.</p>

        <?php if ($error !== ""): ?>
            <div class="error-message" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php" class="login-form">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION["login_csrf_token"], ENT_QUOTES, "UTF-8") ?>"
            >

            <label for="guardian">Guardian name</label>
            <input
                id="guardian"
                name="guardian"
                type="text"
                value="<?= htmlspecialchars($guardianName, ENT_QUOTES, "UTF-8") ?>"
                maxlength="100"
                autocomplete="name"
                placeholder="Name registered with the clinic"
                required
            >

            <label for="phone-number">Phone number</label>
            <input
                id="phone-number"
                name="phone_number"
                type="tel"
                maxlength="30"
                autocomplete="tel"
                placeholder="Phone number registered with the clinic"
                required
            >

            <button type="submit">Sign in</button>
        </form>

        <p class="privacy-note">
            Use the guardian name and phone number recorded by your clinic. Your details are matched
            against existing patient records.
        </p>
        <a class="home-link" href="../user_folder/index.php">Back to ImmuCare</a>
    </section>
</main>
</body>
</html>
