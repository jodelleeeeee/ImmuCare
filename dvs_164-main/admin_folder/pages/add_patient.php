<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Patient | ImmuCare</title>

    <link rel="stylesheet" href="../style.css">

    <style>

        .form-container {
            max-width: 750px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .form-container h1 {
            margin-bottom: 25px;
            color: #315f68;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #416f72;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #dcebea;
            border-radius: 9px;
            outline: none;
        }

        .submit-btn {
            margin-top: 20px;
            background: #58af98;
            color: white;
            border: none;
            padding: 13px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .cancel-btn {
            margin-left: 10px;
            text-decoration: none;
            color: #677f82;
        }

        @media (max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }
        }

    </style>

</head>

<body>

<div class="form-container">

    <h1>Add Patient</h1>

    <form
        action="../backend/add_patient.php"
        method="POST"
    >

        <div class="form-grid">

            <div class="form-group full">
                <label>Baby Name</label>

                <input
                    type="text"
                    name="baby_name"
                    required
                >
            </div>

            <div class="form-group">
                <label>Age</label>

                <input
                    type="number"
                    name="age"
                    min="0"
                    required
                >
            </div>

            <div class="form-group">
                <label>Birthday</label>

                <input
                    type="date"
                    name="birthday"
                    required
                >
            </div>

            <div class="form-group">
                <label>Guardian</label>

                <input
                    type="text"
                    name="guardian"
                    required
                >
            </div>

            <div class="form-group">
                <label>Relationship</label>

                <input
                    type="text"
                    name="relationship"
                    placeholder="Mother, Father, Guardian..."
                    required
                >
            </div>

            <div class="form-group">
                <label>Phone Number</label>

                <input
                    type="text"
                    name="phone_number"
                    required
                >
            </div>

            <div class="form-group">
                <label>Last Vaccine</label>

                <input
                    type="text"
                    name="last_vaccine"
                    placeholder="Example: BCG"
                >
            </div>

            <div class="form-group">
                <label>Status</label>

                <select name="status" required>

                    <option value="">
                        Select Status
                    </option>

                    <option value="Up to Date">
                        Up to Date
                    </option>

                    <option value="Pending">
                        Pending
                    </option>

                    <option value="Overdue">
                        Overdue
                    </option>

                    <option value="Completed">
                        Completed
                    </option>

                </select>
            </div>

            <div class="form-group">
                <label>Next Vaccine</label>

                <input
                    type="text"
                    name="next_vaccine"
                    placeholder="Example: Pentavalent 1"
                >
            </div>

            <div class="form-group">
                <label>Next Visit</label>

                <input
                    type="date"
                    name="next_visit"
                >
            </div>

        </div>

        <button
            type="submit"
            class="submit-btn"
        >
            Save Patient
        </button>

        <a
            href="records.php"
            class="cancel-btn"
        >
            Cancel
        </a>

    </form>

</div>

</body>

</html>