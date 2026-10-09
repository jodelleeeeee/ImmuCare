<?php

require_once "../connect.php";

$result = $mysqli->query("
    SELECT *
    FROM patients
    ORDER BY id ASC
");

$patients = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Patient Records | ImmuCare</title>

    <link rel="stylesheet" href="../style.css">

    <style>

        .records-container {
            padding: 20px;
            overflow-y: auto;
        }

        .records-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .records-header h1 {
            font-size: 25px;
            color: #315f68;
        }

        .add-btn {
            background: #61bca4;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .add-btn:hover {
            background: #459d86;
        }

        .records-table-wrapper {
            background: white;
            border-radius: 15px;
            padding: 20px;
            overflow-x: auto;
            box-shadow: 0 4px 12px rgba(70, 110, 110, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        th {
            text-align: left;
            padding: 14px;
            background: #e8f8f3;
            color: #315f68;
            white-space: nowrap;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #edf2f1;
            color: #55777d;
            white-space: nowrap;
        }

        tr:hover {
            background: #f8fcfb;
        }

        .edit-btn,
        .delete-btn {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .edit-btn {
            background: #e4f5ff;
            color: #377a99;
        }

        .delete-btn {
            background: #ffe7ec;
            color: #b65369;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            background: #e8f8f3;
            color: #39776f;
        }

    </style>

</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <div class="logo">

            <img
                src="../images/logo.png"
                alt="ImmuCare"
                width="150"
            >

        </div>

        <nav>

            <a href="../index.php">
                Dashboard
            </a>

            <a
                href="records.php"
                class="active"
            >
                Records
            </a>

            <a href="guardian_accounts.php">
                Guardian Accounts
            </a>

            <a href="management.php">
                Management
            </a>

            <a href="monitoring.php">
                Monitoring
            </a>

            <a href="announcements.php">
                Announcements
            </a>

            <a href="reports.php">
                Reports
            </a>

            <a href="settings.php">
                Settings
            </a>

        </nav>

    </aside>

    <main class="main">

        <header class="topbar">

            <input
                type="text"
                placeholder="🔍 Search..."
                class="search"
                id="searchInput"
            >

            <div class="profile">
                🔔 👤 Profile
            </div>

        </header>

        <div class="records-container">

            <div class="records-header">

                <h1>
                    Patient Records
                </h1>

                <a
                    href="add-patient.php"
                    class="add-btn"
                >
                    + Add Patient
                </a>

            </div>

            <div class="records-table-wrapper">

                <table id="patientsTable">

                    <thead>

                    <tr>

                        <th>ID</th>
                        <th>Baby Name</th>
                        <th>Age</th>
                        <th>Birthday</th>
                        <th>Guardian</th>
                        <th>Relationship</th>
                        <th>Phone Number</th>
                        <th>Last Vaccine</th>
                        <th>Status</th>
                        <th>Next Vaccine</th>
                        <th>Next Visit</th>
                        <th>Actions</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php if (count($patients) > 0): ?>

                        <?php foreach ($patients as $patient): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($patient["id"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["baby_name"] ?? "") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["age"] ?? "") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["birthday"] ?? "") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["guardian"] ?? "") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["relationship"] ?? "") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["phone_number"] ?? "") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["last_vaccine"] ?? "") ?>
                                </td>

                                <td>

                                    <span class="status-badge">

                                        <?= htmlspecialchars($patient["status"] ?? "") ?>

                                    </span>

                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["next_vaccine"] ?? "") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($patient["next_visit"] ?? "") ?>
                                </td>

                                <td>

                                    <a
                                        class="edit-btn"
                                        href="edit-patient.php?id=<?= $patient["id"] ?>"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        class="delete-btn"
                                        href="../backend/delete_patient.php?id=<?= $patient["id"] ?>"
                                        onclick="return confirm('Delete this patient?')"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="12">
                                No patient records found.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

<script>

const searchInput = document.getElementById("searchInput");

searchInput.addEventListener("keyup", function () {

    const searchValue = this.value.toLowerCase();

    const rows = document.querySelectorAll(
        "#patientsTable tbody tr"
    );

    rows.forEach(function (row) {

        const rowText = row.textContent.toLowerCase();

        if (rowText.includes(searchValue)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });

});

</script>

</body>

</html>