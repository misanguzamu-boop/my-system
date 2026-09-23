<?php

ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

/*
|--------------------------------------------------------------------------
| ARCHITECT ACCESS CONTROL
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'architect'
) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| CREATE CONTACT TABLE
|--------------------------------------------------------------------------
*/

$createTable = $conn->query("
    CREATE TABLE IF NOT EXISTS admin_contact (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_name VARCHAR(150) NOT NULL,
        whatsapp VARCHAR(30) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        location VARCHAR(255) NOT NULL,
        description TEXT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");


/*
|--------------------------------------------------------------------------
| INSERT DEFAULT CONTACT IF EMPTY
|--------------------------------------------------------------------------
*/

$checkContact = $conn->query(
    "SELECT id FROM admin_contact ORDER BY id ASC LIMIT 1"
);

if (
    $checkContact &&
    $checkContact->num_rows === 0
) {

    $stmt = $conn->prepare("
        INSERT INTO admin_contact
        (
            admin_name,
            whatsapp,
            phone,
            location,
            description
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    if ($stmt) {

        $defaultName =
            "Shema Shigila Daudi";

        $defaultWhatsapp =
            "0740220311";

        $defaultPhone =
            "0681804661";

        $defaultLocation =
            "Mbeya, Tanzania";

        $defaultDescription =
            "Professional architectural design, building plans, custom layouts and architectural consultation services.";

        $stmt->bind_param(
            "sssss",
            $defaultName,
            $defaultWhatsapp,
            $defaultPhone,
            $defaultLocation,
            $defaultDescription
        );

        $stmt->execute();

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| UPDATE CONTACT
|--------------------------------------------------------------------------
*/

$success = "";
$error = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['update_contact'])
) {

    $adminName =
        trim($_POST['admin_name'] ?? '');

    $whatsapp =
        trim($_POST['whatsapp'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $location =
        trim($_POST['location'] ?? '');

    $description =
        trim($_POST['description'] ?? '');


    if (
        $adminName === "" ||
        $whatsapp === "" ||
        $phone === "" ||
        $location === ""
    ) {

        $error =
            "Tafadhali jaza jina, WhatsApp, simu na location.";

    } else {

        $stmt = $conn->prepare("
            UPDATE admin_contact
            SET
                admin_name = ?,
                whatsapp = ?,
                phone = ?,
                location = ?,
                description = ?
            WHERE id = (
                SELECT id FROM (
                    SELECT id
                    FROM admin_contact
                    ORDER BY id ASC
                    LIMIT 1
                ) AS temp
            )
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sssss",
                $adminName,
                $whatsapp,
                $phone,
                $location,
                $description
            );

            if ($stmt->execute()) {

                $success =
                    "✓ Taarifa za mawasiliano zimebadilishwa kwa mafanikio.";

            } else {

                $error =
                    "Samahani, taarifa hazikuweza kusasishwa.";
            }

            $stmt->close();

        } else {

            $error =
                "Database error: taarifa hazikuweza kusasishwa.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET CURRENT CONTACT
|--------------------------------------------------------------------------
*/

$contact = [
    'admin_name' => 'Shema Shigila Daudi',
    'whatsapp' => '0740220311',
    'phone' => '0681804661',
    'location' => 'Mbeya, Tanzania',
    'description' => ''
];

$result = $conn->query("
    SELECT *
    FROM admin_contact
    ORDER BY id ASC
    LIMIT 1
");

if ($result && $result->num_rows > 0) {

    $contact =
        $result->fetch_assoc();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Manage Contact | Architect
    </title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family:
                "Segoe UI",
                system-ui,
                sans-serif;

            background:
                #0f172a;

            color:
                #cbd5e1;
        }

        .header {

            background:
                #1e293b;

            border-bottom:
                1px solid #334155;

            padding:
                18px 30px;

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            gap:
                15px;
        }

        .header h1 {

            margin: 0;

            color:
                #a855f7;

            font-size:
                21px;
        }

        .header a {

            color:
                #38bdf8;

            text-decoration:
                none;

            font-weight:
                bold;

            font-size:
                14px;
        }

        .container {

            width:
                min(1000px, 92%);

            margin:
                40px auto;
        }

        .card {

            background:
                #1e293b;

            border:
                1px solid #334155;

            border-radius:
                16px;

            padding:
                30px;

            box-shadow:
                0 20px 50px rgba(0,0,0,.25);
        }

        .card h2 {

            margin-top:
                0;

            color:
                #f8fafc;
        }

        .intro {

            color:
                #94a3b8;

            line-height:
                1.7;

            margin-bottom:
                25px;
        }

        .message {

            padding:
                13px 15px;

            border-radius:
                8px;

            margin-bottom:
                20px;

            font-weight:
                600;
        }

        .success {

            background:
                #14532d;

            color:
                #dcfce7;

            border:
                1px solid #166534;
        }

        .error {

            background:
                #7f1d1d;

            color:
                #fee2e2;

            border:
                1px solid #991b1b;
        }

        .form-grid {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                18px;
        }

        .form-group {

            display:
                flex;

            flex-direction:
                column;
        }

        .full {

            grid-column:
                1 / -1;
        }

        label {

            color:
                #94a3b8;

            font-size:
                13px;

            font-weight:
                700;

            margin-bottom:
                7px;
        }

        input,
        textarea {

            width:
                100%;

            padding:
                13px;

            border:
                1px solid #475569;

            border-radius:
                8px;

            background:
                #0f172a;

            color:
                white;

            outline:
                none;

            font-size:
                14px;
        }

        input:focus,
        textarea:focus {

            border-color:
                #38bdf8;
        }

        textarea {

            min-height:
                130px;

            resize:
                vertical;
        }

        .btn {

            width:
                100%;

            padding:
                14px;

            margin-top:
                20px;

            border:
                none;

            border-radius:
                9px;

            background:
                #a855f7;

            color:
                white;

            font-weight:
                800;

            cursor:
                pointer;

            font-size:
                14px;
        }

        .btn:hover {

            background:
                #9333ea;
        }

        .preview {

            margin-top:
                30px;

            padding:
                25px;

            border:
                1px solid #334155;

            border-radius:
                12px;

            background:
                #0f172a;
        }

        .preview h3 {

            color:
                #38bdf8;

            margin-top:
                0;
        }

        .preview-row {

            padding:
                10px 0;

            border-bottom:
                1px solid #1e293b;
        }

        .preview-row:last-child {

            border-bottom:
                none;
        }

        .preview-row strong {

            color:
                #f8fafc;
        }

        @media (max-width: 700px) {

            .header {

                flex-direction:
                    column;

                align-items:
                    flex-start;
            }

            .form-grid {

                grid-template-columns:
                    1fr;
            }

            .full {

                grid-column:
                    auto;
            }

            .card {

                padding:
                    20px;
            }
        }

    </style>

</head>

<body>


<div class="header">

    <h1>
        📞 MANAGE ADMIN CONTACT
    </h1>

    <div>

        <a href="architect_dashboard.php">
            ← Dashboard
        </a>

        &nbsp;&nbsp;

        <a href="contact.php">
            View Contact
        </a>

    </div>

</div>


<div class="container">

    <div class="card">

        <h2>
            Admin Contact Information
        </h2>

        <p class="intro">
            Hapa unaweza kubadilisha taarifa ambazo
            mteja ataona kwenye ukurasa wa mawasiliano.
            Huhitaji kugusa PHP code.
        </p>


        <?php if ($success !== ""): ?>

            <div class="message success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="message error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <input
                type="hidden"
                name="update_contact"
                value="1"
            >


            <div class="form-grid">


                <div class="form-group">

                    <label>
                        Admin / Architect Name
                    </label>

                    <input
                        type="text"
                        name="admin_name"
                        value="<?= htmlspecialchars($contact['admin_name'] ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        WhatsApp Number
                    </label>

                    <input
                        type="text"
                        name="whatsapp"
                        value="<?= htmlspecialchars($contact['whatsapp'] ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Normal Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="<?= htmlspecialchars($contact['phone'] ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Location
                    </label>

                    <input
                        type="text"
                        name="location"
                        value="<?= htmlspecialchars($contact['location'] ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group full">

                    <label>
                        Description / Introduction
                    </label>

                    <textarea
                        name="description"
                        placeholder="Andika maelezo ya huduma zako..."
                    ><?= htmlspecialchars($contact['description'] ?? '') ?></textarea>

                </div>

            </div>


            <button
                type="submit"
                class="btn"
            >

                💾 Save Contact Information

            </button>

        </form>


        <div class="preview">

            <h3>
                👁️ Current Information
            </h3>

            <div class="preview-row">

                <strong>
                    Name:
                </strong>

                <?= htmlspecialchars($contact['admin_name']) ?>

            </div>

            <div class="preview-row">

                <strong>
                    WhatsApp:
                </strong>

                <?= htmlspecialchars($contact['whatsapp']) ?>

            </div>

            <div class="preview-row">

                <strong>
                    Phone:
                </strong>

                <?= htmlspecialchars($contact['phone']) ?>

            </div>

            <div class="preview-row">

                <strong>
                    Location:
                </strong>

                <?= htmlspecialchars($contact['location']) ?>

            </div>

        </div>

    </div>

</div>

</body>

</html>