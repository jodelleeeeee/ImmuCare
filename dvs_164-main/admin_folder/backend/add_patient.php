<?php

require_once "../connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $baby_name = trim($_POST["baby_name"]);
    $age = (int) $_POST["age"];
    $birthday = $_POST["birthday"];
    $guardian = trim($_POST["guardian"]);
    $relationship = trim($_POST["relationship"]);
    $phone_number = trim($_POST["phone_number"]);
    $last_vaccine = trim($_POST["last_vaccine"] ?? "");
    $status = trim($_POST["status"]);
    $next_vaccine = trim($_POST["next_vaccine"] ?? "");
    $next_visit = $_POST["next_visit"] ?? null;

    if (
        empty($baby_name) ||
        empty($birthday) ||
        empty($guardian) ||
        empty($relationship) ||
        empty($phone_number) ||
        empty($status)
    ) {
        die("Please complete all required fields.");
    }

    if ($next_visit === "") {
        $next_visit = null;
    }

    $stmt = $mysqli->prepare("
        INSERT INTO patients
        (
            baby_name,
            age,
            birthday,
            guardian,
            relationship,
            phone_number,
            last_vaccine,
            status,
            next_vaccine,
            next_visit
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sissssssss",
        $baby_name,
        $age,
        $birthday,
        $guardian,
        $relationship,
        $phone_number,
        $last_vaccine,
        $status,
        $next_vaccine,
        $next_visit
    );

    $stmt->execute();

    header("Location: ../pages/records.php");
    exit;
}
?>