<?php

require_once __DIR__ . "/common.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/appointments.php");
    exit;
}

$guardian = currentGuardian();
if ($guardian === "") {
    http_response_code(401);
    exit("Sign in before requesting an appointment.");
}

if (!validateCsrfToken()) {
    http_response_code(400);
    exit("The form expired. Reload the page and try again.");
}

$patientId = filter_var($_POST["patient_id"] ?? null, FILTER_VALIDATE_INT);
$date = $_POST["appointment_date"] ?? "";
$time = $_POST["appointment_time"] ?? "";
$notes = $_POST["notes"] ?? "";
$notesIsString = is_string($notes);
$notes = $notesIsString ? trim($notes) : "";
$dateObject = is_string($date) ? DateTimeImmutable::createFromFormat("!Y-m-d", $date) : false;
$dateErrors = DateTimeImmutable::getLastErrors();
$timeObject = is_string($time) ? DateTimeImmutable::createFromFormat("!H:i", $time) : false;
$timeErrors = DateTimeImmutable::getLastErrors();

if (
    !$patientId
    || !is_string($date)
    || !$dateObject
    || ($dateErrors !== false && ($dateErrors["warning_count"] > 0 || $dateErrors["error_count"] > 0))
    || $dateObject->format("Y-m-d") !== $date
    || $date < date("Y-m-d")
    || !is_string($time)
    || !$timeObject
    || ($timeErrors !== false && ($timeErrors["warning_count"] > 0 || $timeErrors["error_count"] > 0))
    || $timeObject->format("H:i") !== $time
    || !$notesIsString
    || strlen($notes) > 500
) {
    setUserFlash("Enter a valid patient, date, time, and note (up to 500 characters).", "error");
    header("Location: ../pages/appointments.php");
    exit;
}

$patientStmt = $mysqli->prepare("
    SELECT id
    FROM patients
    WHERE id = ? AND guardian = ?
    LIMIT 1
");
$patientStmt->bind_param("is", $patientId, $guardian);
$patientStmt->execute();
$patient = $patientStmt->get_result()->fetch_assoc();
$patientStmt->close();

if (!$patient) {
    http_response_code(403);
    exit("That patient is not linked to your account.");
}

$stmt = $mysqli->prepare("
    INSERT INTO appointment_requests
        (patient_id, guardian, appointment_date, appointment_time, notes)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("issss", $patientId, $guardian, $date, $time, $notes);
$stmt->execute();
$stmt->close();

setUserFlash("Your appointment request was sent to the clinic.");
header("Location: ../pages/appointments.php");
exit;
