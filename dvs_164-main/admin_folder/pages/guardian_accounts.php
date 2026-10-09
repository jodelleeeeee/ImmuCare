<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    exit("Staff access is required.");
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../../user_folder/backend/guardian_accounts.php";
ensureGuardianAccountsTable($mysqli);

if (!isset($_SESSION["guardian_accounts_csrf"]) || !is_string($_SESSION["guardian_accounts_csrf"])) {
    $_SESSION["guardian_accounts_csrf"] = bin2hex(random_bytes(32));
}

$error = "";
$success = "";
$selectedGuardian = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedToken = $_POST["csrf_token"] ?? "";
    $selectedGuardian = $_POST["guardian"] ?? "";
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if (
        !is_string($submittedToken)
        || !hash_equals($_SESSION["guardian_accounts_csrf"], $submittedToken)
    ) {
        http_response_code(400);
        $error = "This form expired. Refresh the page and try again.";
    } elseif (
        !is_string($selectedGuardian)
        || !is_string($username)
        || !is_string($password)
        || trim($selectedGuardian) === ""
        || trim($username) === ""
        || strlen(trim($username)) < 3
        || strlen(trim($username)) > 100
        || strlen($password) < 8
        || strlen($password) > 72
    ) {
        $error = "Choose a guardian, enter a username (3-100 characters), and set a password (8-72 characters).";
    } else {
        $selectedGuardian = trim($selectedGuardian);
        $username = trim($username);

        $guardianCheck = $mysqli->prepare("
            SELECT 1
            FROM patients
            WHERE guardian = ?
            LIMIT 1
        ");
        $guardianCheck->bind_param("s", $selectedGuardian);
        $guardianCheck->execute();
        $guardianExists = $guardianCheck->get_result()->num_rows > 0;
        $guardianCheck->close();

        if (!$guardianExists) {
            $error = "The selected guardian has no clinic patient record.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $saveAccount = $mysqli->prepare("
                INSERT INTO guardian_accounts (guardian, username, password_hash)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    username = VALUES(username),
                    password_hash = VALUES(password_hash)
            ");
            $saveAccount->bind_param("sss", $selectedGuardian, $username, $passwordHash);

            try {
                $saveAccount->execute();
                $success = "Login credentials saved for " . $selectedGuardian . ".";
                $username = "";
                $_SESSION["guardian_accounts_csrf"] = bin2hex(random_bytes(32));
            } catch (mysqli_sql_exception $exception) {
                if ((int) $exception->getCode() === 1062) {
                    $error = "That username is already assigned to another guardian. Choose a different username.";
                } else {
                    throw $exception;
                }
            } finally {
                $saveAccount->close();
            }
        }
    }
}

$guardians = $mysqli->query("
    SELECT p.guardian, ga.username
    FROM (
        SELECT DISTINCT guardian
        FROM patients
    ) AS p
    LEFT JOIN guardian_accounts AS ga ON ga.guardian = p.guardian
    ORDER BY p.guardian
")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardian Login Accounts | ImmuCare</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .account-form {
            max-width: 680px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .account-form h1 {
            margin-bottom: 10px;
            color: #315f68;
        }

        .account-form p {
            margin-bottom: 22px;
            color: #677f82;
            line-height: 1.5;
        }

        .account-form label {
            display: block;
            margin: 16px 0 7px;
            color: #416f72;
            font-weight: bold;
        }

        .account-form input,
        .account-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #dcebea;
            border-radius: 9px;
        }

        .account-form button {
            margin-top: 22px;
            padding: 13px 22px;
            border: 0;
            border-radius: 10px;
            background: #58af98;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        .notice {
            margin: 15px 0;
            padding: 12px 14px;
            border-radius: 9px;
        }

        .notice.error {
            background: #fff4f7;
            color: #865969;
        }

        .notice.success {
            background: #e8f8f3;
            color: #315f68;
        }

        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #315f68;
        }
    </style>
</head>
<body>
    <main class="account-form">
        <h1>Guardian Login Accounts</h1>
        <p>Set or reset the username and password a guardian will use to access the patient records linked to their name.</p>

        <?php if ($error !== ""): ?>
            <div class="notice error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></div>
        <?php endif; ?>
        <?php if ($success !== ""): ?>
            <div class="notice success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?></div>
        <?php endif; ?>

        <?php if ($guardians === []): ?>
            <div class="notice error">No guardians have patient records yet. Add a patient record first.</div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["guardian_accounts_csrf"], ENT_QUOTES, "UTF-8") ?>">

                <label for="guardian">Guardian</label>
                <select id="guardian" name="guardian" required>
                    <option value="">Select a guardian</option>
                    <?php foreach ($guardians as $record): ?>
                        <?php $recordGuardian = (string) $record["guardian"]; ?>
                        <option
                            value="<?= htmlspecialchars($recordGuardian, ENT_QUOTES, "UTF-8") ?>"
                            <?= $selectedGuardian === $recordGuardian ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($recordGuardian, ENT_QUOTES, "UTF-8") ?>
                            <?= !empty($record["username"]) ? " (username: " . htmlspecialchars((string) $record["username"], ENT_QUOTES, "UTF-8") . ")" : " (not configured)" ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="username">Username</label>
                <input
                    id="username"
                    name="username"
                    type="text"
                    minlength="3"
                    maxlength="100"
                    autocomplete="off"
                    value="<?= htmlspecialchars($username, ENT_QUOTES, "UTF-8") ?>"
                    required
                >

                <label for="password">New password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >

                <button type="submit">Save Login Credentials</button>
            </form>
        <?php endif; ?>

        <a class="back-link" href="records.php">Back to Patient Records</a>
    </main>
</body>
</html>
