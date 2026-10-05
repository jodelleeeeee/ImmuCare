<?php

require_once "../connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../pages/records.php");
    exit;

}


/* =========================
   GET FORM DATA
========================= */

$id = (int) ($_POST["id"] ?? 0);

$baby_name = trim($_POST["baby_name"] ?? "");

$age = (int) ($_POST["age"] ?? 0);

$birthday = $_POST["birthday"] ?? "";

$guardian = trim($_POST["guardian"] ?? "");

$relationship =
    trim($_POST["relationship"] ?? "");

$phone_number =
    trim($_POST["phone_number"] ?? "");

$last_vaccine =
    trim($_POST["last_vaccine"] ?? "");

$status =
    trim($_POST["status"] ?? "");

$next_vaccine =
    trim($_POST["next_vaccine"] ?? "");

$next_visit =
    $_POST["next_visit"] ?? null;


/* =========================
   VALIDATION
========================= */

if (
    $id <= 0 ||
    empty($baby_name) ||
    empty($birthday) ||
    empty($guardian) ||
    empty($relationship) ||
    empty($phone_number) ||
    empty($status)
) {

    die(
        "Please complete all required fields."
    );

}


/* =========================
   OPTIONAL NEXT VISIT
========================= */

if ($next_visit === "") {

    $next_visit = null;

}


/* =========================
   UPDATE DATABASE
========================= */

$stmt = $mysqli->prepare("
    UPDATE patients

    SET

        baby_name = ?,
        age = ?,
        birthday = ?,
        guardian = ?,
        relationship = ?,
        phone_number = ?,
        last_vaccine = ?,
        status = ?,
        next_vaccine = ?,
        next_visit = ?

    WHERE id = ?
");


$stmt->bind_param(
    "sissssssssi",

    $baby_name,
    $age,
    $birthday,
    $guardian,
    $relationship,
    $phone_number,
    $last_vaccine,
    $status,
    $next_vaccine,
    $next_visit,
    $id
);


$stmt->execute();


/* =========================
   RETURN TO RECORDS
========================= */

header(
    "Location: ../pages/records.php"
);

exit;

?>