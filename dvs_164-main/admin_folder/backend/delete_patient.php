<?php

require_once "../connect.php";

if (!isset($_GET["id"])) {

    header(
        "Location: ../pages/records.php"
    );

    exit;

}

$id = (int) $_GET["id"];

$stmt = $mysqli->prepare("
    DELETE FROM patients
    WHERE id = ?
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

header(
    "Location: ../pages/records.php"
);

exit;
?>