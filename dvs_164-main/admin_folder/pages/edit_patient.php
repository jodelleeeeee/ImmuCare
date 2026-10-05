<?php

require_once "../connect.php";

if (!isset($_GET["id"])) {
    header("Location: records.php");
    exit;
}

$id = (int) $_GET["id"];

$stmt = $mysqli->prepare("
    SELECT *
    FROM patients
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    die("Patient not found.");
}

?>

<?php

$id = $_GET["id"] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Patient | ImmuCare</title>

    <link rel="stylesheet" href="../style.css">
</head>

<body>

<div style="
    max-width:700px;
    margin:50px auto;
    background:white;
    padding:30px;
    border-radius:15px;
">

    <h1>Edit Patient</h1>

    <p>
        Patient ID:
        <?= htmlspecialchars($id ?? "") ?>
    </p>

    <p>
        Editing functionality will be added later.
    </p>

    <br>

    <a href="records.php">
        ← Back to Records
    </a>

</div>

</body>
</html>