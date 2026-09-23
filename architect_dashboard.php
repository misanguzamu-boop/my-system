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
| SUCCESS / ERROR MESSAGES
|--------------------------------------------------------------------------
*/

$upload_msg = "";
$process_msg = "";
$error_msg = "";


/*
|--------------------------------------------------------------------------
| 1. UPLOAD NEW ARCHITECTURAL PROJECT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['upload_work'])
) {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $cost = trim($_POST['estimated_cost'] ?? '');

    $image_path = "";


    if (
        $title === "" ||
        $category === "" ||
        $desc === "" ||
        $cost === ""
    ) {

        $error_msg =
            "Tafadhali jaza taarifa zote za project.";

    } elseif (
        !isset($_FILES['design_image']) ||
        $_FILES['design_image']['error'] !== UPLOAD_ERR_OK
    ) {

        $error_msg =
            "Tafadhali chagua picha ya architectural design.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | IMAGE UPLOAD
        |--------------------------------------------------------------------------
        */

        $target_dir = "portfolio_uploads/";

        if (!is_dir($target_dir)) {

            mkdir($target_dir, 0777, true);
        }


        $original_name =
            $_FILES['design_image']['name'];

        $tmp_name =
            $_FILES['design_image']['tmp_name'];

        $file_size =
            $_FILES['design_image']['size'];


        $extension =
            strtolower(
                pathinfo(
                    $original_name,
                    PATHINFO_EXTENSION
                )
            );


        $allowed_extensions = [
            'jpg',
            'jpeg',
            'png',
            'webp'
        ];


        if (
            !in_array(
                $extension,
                $allowed_extensions,
                true
            )
        ) {

            $error_msg =
                "Aina ya picha hairuhusiwi. Tumia JPG, JPEG, PNG au WEBP.";

        } elseif ($file_size > 5 * 1024 * 1024) {

            $error_msg =
                "Picha ni kubwa sana. Maximum ni 5MB.";

        } else {

            $new_filename =
                time() .
                "_" .
                uniqid() .
                "." .
                $extension;


            $target_file =
                $target_dir .
                $new_filename;


            if (
                move_uploaded_file(
                    $tmp_name,
                    $target_file
                )
            ) {

                $image_path =
                    $target_file;


                /*
                |--------------------------------------------------------------------------
                | SAVE DESIGN TO DATABASE
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare(
                    "INSERT INTO designs
                    (
                        title,
                        category,
                        description,
                        image_path,
                        estimated_cost
                    )
                    VALUES (?, ?, ?, ?, ?)"
                );


                if ($stmt) {

                    $stmt->bind_param(
                        "sssss",
                        $title,
                        $category,
                        $desc,
                        $image_path,
                        $cost
                    );


                    if ($stmt->execute()) {

                        $upload_msg =
                            "Mradi mpya umepakiwa na kuwekwa kwenye Portfolio kwa mafanikio!";

                    } else {

                        $error_msg =
                            "Database error: Mradi haukuweza kuhifadhiwa.";

                        /*
                        | Delete uploaded image if DB failed
                        */

                        if (file_exists($target_file)) {

                            unlink($target_file);
                        }
                    }


                    $stmt->close();

                } else {

                    $error_msg =
                        "Database statement haikuweza kutengenezwa.";

                    if (file_exists($target_file)) {

                        unlink($target_file);
                    }
                }

            } else {

                $error_msg =
                    "Picha haikuweza kupakiwa kwenye server.";
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| 2. PROCESS CUSTOMER ORDER
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['process_order'])
) {

    $order_id =
        intval($_POST['order_id'] ?? 0);

    $status =
        trim($_POST['status_update'] ?? '');

    $reply =
        trim($_POST['architect_reply'] ?? '');


    if (
        $order_id <= 0 ||
        $status === "" ||
        $reply === ""
    ) {

        $error_msg =
            "Tafadhali chagua status na uandike ujumbe kwa mteja.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE orders
             SET status = ?,
                 architect_reply = ?
             WHERE id = ?"
        );


        if ($stmt) {

            $stmt->bind_param(
                "ssi",
                $status,
                $reply,
                $order_id
            );


            if ($stmt->execute()) {

                $process_msg =
                    "Majibu yako yamehifadhiwa na kutumwa kwa mteja husika.";

            } else {

                $error_msg =
                    "Samahani, majibu hayakuweza kuhifadhiwa.";
            }


            $stmt->close();

        } else {

            $error_msg =
                "Database error wakati wa kusasisha order.";
        }
    }
}


/*
|--------------------------------------------------------------------------
| 3. GET CUSTOMER ORDERS
|--------------------------------------------------------------------------
*/

$orders_res = $conn->query(
    "SELECT
        o.*,
        u.fullname,
        u.email,
        d.title AS design_title
     FROM orders o
     JOIN users u
        ON o.customer_id = u.id
     JOIN designs d
        ON o.design_id = d.id
     ORDER BY o.id DESC"
);

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
        Architect Console | Manager Dashboard
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            font-family:
                'Segoe UI',
                system-ui,
                sans-serif;

            background: #0f172a;

            color: #cbd5e1;

            margin: 0;

            padding: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .header {

            background: #1e293b;

            border-bottom:
                1px solid #334155;

            padding: 15px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;
        }


        .header h1 {

            margin: 0;

            font-size: 20px;

            color: #a855f7;
        }


        .header-right {

            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 15px;
        }


        .header-right a {

            color: #38bdf8;

            text-decoration: none;

            font-weight: bold;

            font-size: 14px;
        }


        .header-right a:hover {

            text-decoration: underline;
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN WORKSPACE
        |--------------------------------------------------------------------------
        */

        .workspace-grid {

            display: flex;

            padding: 25px;

            gap: 25px;

            align-items: flex-start;
        }


        /*
        |--------------------------------------------------------------------------
        | UPLOADER
        |--------------------------------------------------------------------------
        */

        .panel-uploader {

            background: #1e293b;

            padding: 25px;

            border-radius: 12px;

            width: 35%;

            border:
                1px solid #334155;

            height: fit-content;
        }


        /*
        |--------------------------------------------------------------------------
        | ORDERS
        |--------------------------------------------------------------------------
        */

        .panel-orders {

            background: #1e293b;

            border-radius: 12px;

            border:
                1px solid #334155;

            width: 65%;

            padding: 25px;
        }


        /*
        |--------------------------------------------------------------------------
        | LABELS
        |--------------------------------------------------------------------------
        */

        label {

            font-size: 13px;

            font-weight: 600;

            text-transform: uppercase;

            color: #94a3b8;

            display: block;

            margin-top: 12px;
        }


        /*
        |--------------------------------------------------------------------------
        | FORM INPUTS
        |--------------------------------------------------------------------------
        */

        input,
        select,
        textarea {

            width: 100%;

            padding: 10px;

            margin-top: 5px;

            border-radius: 6px;

            border:
                1px solid #475569;

            background: #0f172a;

            color: white;

            font-size: 14px;

            outline: none;
        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color: #a855f7;
        }


        textarea {

            resize: vertical;

            min-height: 100px;
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTONS
        |--------------------------------------------------------------------------
        */

        .btn-action {

            width: 100%;

            padding: 12px;

            background: #a855f7;

            color: white;

            border: none;

            border-radius: 6px;

            font-weight: bold;

            cursor: pointer;

            margin-top: 15px;

            font-size: 14px;
        }


        .btn-action:hover {

            background: #9333ea;
        }


        .btn-update {

            background: #16a34a;

            color: white;

            padding: 10px 18px;

            border: none;

            border-radius: 5px;

            font-weight: bold;

            cursor: pointer;

            font-size: 13px;

            white-space: nowrap;
        }


        .btn-update:hover {

            background: #15803d;
        }


        /*
        |--------------------------------------------------------------------------
        | ORDER CARD
        |--------------------------------------------------------------------------
        */

        .order-card {

            background: #0f172a;

            border:
                1px solid #334155;

            border-radius: 8px;

            padding: 20px;

            margin-bottom: 20px;
        }


        .order-header {

            display: flex;

            justify-content: space-between;

            gap: 15px;

            border-bottom:
                1px solid #223047;

            padding-bottom: 10px;

            margin-bottom: 12px;
        }


        /*
        |--------------------------------------------------------------------------
        | MESSAGE BOX
        |--------------------------------------------------------------------------
        */

        .msg-box {

            background: #15803d;

            color: white;

            padding: 12px;

            border-radius: 6px;

            font-size: 13px;

            margin-bottom: 15px;

            font-weight: bold;

            border:
                1px solid #166534;
        }


        .error-box {

            background: #991b1b;

            color: white;

            padding: 12px;

            border-radius: 6px;

            font-size: 13px;

            margin-bottom: 15px;

            font-weight: bold;

            border:
                1px solid #7f1d1d;
        }


        /*
        |--------------------------------------------------------------------------
        | CUSTOMER INFO
        |--------------------------------------------------------------------------
        */

        .customer-info {

            font-size: 14px;

            margin-bottom: 15px;

            background: #1e293b;

            padding: 12px;

            border-radius: 6px;

            border:
                1px solid #2b394f;

            line-height: 1.6;
        }


        /*
        |--------------------------------------------------------------------------
        | ORDER FORM
        |--------------------------------------------------------------------------
        */

        .order-form {

            display: grid;

            grid-template-columns:
                1fr 2fr auto;

            gap: 15px;

            align-items: end;
        }


        .form-group {

            min-width: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {

            text-align: center;

            margin-top: 40px;

            padding: 20px;

            color: #64748b;

            font-size: 13px;

            border-top:
                1px solid #1e293b;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 900px) {

            .workspace-grid {

                flex-direction: column;
            }


            .panel-uploader,
            .panel-orders {

                width: 100%;
            }


            .order-form {

                grid-template-columns: 1fr;
            }


            .btn-update {

                width: 100%;
            }
        }


        @media (max-width: 600px) {

            .header {

                flex-direction: column;

                align-items: flex-start;

                padding: 15px 20px;
            }


            .header-right {

                flex-direction: column;

                align-items: flex-start;
            }


            .workspace-grid {

                padding: 15px;
            }


            .panel-uploader,
            .panel-orders {

                padding: 18px;
            }


            .order-header {

                flex-direction: column;
            }
        }

    </style>

</head>


<body>


<!--
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
-->

<div class="header">

    <h1>
        🏛️ ARCHITECT CONSOLE
    </h1>


    <div class="header-right">

        <span style="color:#94a3b8;">

            Architect:

            <strong style="color:#f8fafc;">

                Shema Shingela Daudi

            </strong>

        </span>


        <a href="index.php">

            View Public Home

        </a>


        <a
            href="logout.php"
            style="color:#f87171;"
        >

            Logout

        </a>
        <a href="admin_contact.php">
    📞 Manage Contact
</a>

<a href="admin_delete_design.php">
    🗑️ Delete Designs
</a>

    </div>

</div>


<!--
|--------------------------------------------------------------------------
| WORKSPACE
|--------------------------------------------------------------------------
-->

<div class="workspace-grid">


    <!--
    |--------------------------------------------------------------------------
    | LEFT SIDE - UPLOAD PROJECT
    |--------------------------------------------------------------------------
    -->

    <div class="panel-uploader">

        <h3
            style="
                margin-top:0;
                color:#a855f7;
                border-bottom:1px solid #334155;
                padding-bottom:10px;
            "
        >

            Upload Project Model

        </h3>


        <?php if ($upload_msg !== ""): ?>

            <div class="msg-box">

                ✓
                <?= htmlspecialchars($upload_msg) ?>

            </div>

        <?php endif; ?>


        <?php if ($error_msg !== ""): ?>

            <div class="error-box">

                ⚠️
                <?= htmlspecialchars($error_msg) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <input
                type="hidden"
                name="upload_work"
                value="1"
            >


            <label>
                Project Title
            </label>

            <input
                type="text"
                name="title"
                placeholder="Luxury 4-Bedroom Villa"
                required
            >


            <label>
                Category
            </label>

            <select name="category" required>

                <option value="Residential Building">
                    Residential House / Mjengo wa Familia
                </option>

                <option value="Commercial Complex">
                    Commercial Hub / Majengo ya Biashara
                </option>

                <option value="Church Structure">
                    Church Assembly / Majengo ya Ibada
                </option>

                <option value="Modern Office Structure">
                    Corporate Space / Maofisi
                </option>

            </select>


            <label>
                Specifications Description
            </label>

            <textarea
                name="description"
                placeholder="Sifa za mjengo, vipimo n.k..."
                required
            ></textarea>


            <label>
                Estimated Construction Cost
            </label>

            <input
                type="text"
                name="estimated_cost"
                placeholder="e.g. TZS 45,000,000"
                required
            >


            <label>
                Upload Architectural Render Drawing
            </label>

            <input
                type="file"
                name="design_image"
                accept=".jpg,.jpeg,.png,.webp,image/*"
                required
            >


            <button
                type="submit"
                class="btn-action"
            >

                📐 Publish Blueprint

            </button>

        </form>

    </div>


    <!--
    |--------------------------------------------------------------------------
    | RIGHT SIDE - CUSTOMER ORDERS
    |--------------------------------------------------------------------------
    -->

    <div class="panel-orders">

        <h3
            style="
                margin-top:0;
                color:#38bdf8;
                border-bottom:1px solid #334155;
                padding-bottom:10px;
            "
        >

            Client Custom Requests

        </h3>


        <?php if ($process_msg !== ""): ?>

            <div class="msg-box">

                ✓
                <?= htmlspecialchars($process_msg) ?>

            </div>

        <?php endif; ?>


        <?php if (
            $orders_res &&
            $orders_res->num_rows > 0
        ): ?>


            <?php while (
                $ord = $orders_res->fetch_assoc()
            ): ?>


                <div class="order-card">


                    <!-- ORDER HEADER -->

                    <div class="order-header">

                        <div>

                            <span
                                style="
                                    font-size:12px;
                                    color:#a855f7;
                                    font-weight:bold;
                                    text-transform:uppercase;
                                "
                            >

                                Order Ticket #

                                <?= intval(
                                    $ord['id']
                                ) ?>

                            </span>


                            <h4
                                style="
                                    margin:4px 0 0 0;
                                    font-size:16px;
                                    color:#f8fafc;
                                "
                            >

                                <?= htmlspecialchars(
                                    $ord['design_title']
                                    ?? 'Unknown Design'
                                ) ?>

                            </h4>

                        </div>


                        <div>

                            <span
                                style="
                                    font-size:13px;
                                    color:#94a3b8;
                                "
                            >

                                Status:

                                <strong
                                    style="color:#f59e0b;"
                                >

                                    <?= htmlspecialchars(
                                        $ord['status']
                                        ?? 'Pending Review'
                                    ) ?>

                                </strong>

                            </span>

                        </div>

                    </div>


                    <!-- CUSTOMER DETAILS -->

                    <div class="customer-info">

                        <strong>
                            Mteja:
                        </strong>

                        <?= htmlspecialchars(
                            $ord['fullname']
                            ?? 'Unknown Customer'
                        ) ?>


                        <br>


                        <strong>
                            Email:
                        </strong>

                        <small
                            style="color:#38bdf8;"
                        >

                            <?= htmlspecialchars(
                                $ord['email']
                                ?? ''
                            ) ?>

                        </small>


                        <br>


                        <strong>
                            Plot Size:
                        </strong>

                        <?= htmlspecialchars(
                            $ord['plot_size']
                            ?? ''
                        ) ?>


                        <br>


                        <strong>
                            Maelekezo:
                        </strong>

                        <span
                            style="
                                color:#f1f5f9;
                                font-style:italic;
                            "
                        >

                            <?= nl2br(
                                htmlspecialchars(
                                    $ord['custom_notes']
                                    ?? ''
                                )
                            ) ?>

                        </span>

                    </div>


                    <!--
                    |--------------------------------------------------------------------------
                    | PROCESS ORDER FORM
                    |--------------------------------------------------------------------------
                    -->

                    <form
                        method="POST"
                        class="order-form"
                    >

                        <input
                            type="hidden"
                            name="process_order"
                            value="1"
                        >


                        <input
                            type="hidden"
                            name="order_id"
                            value="<?= intval(
                                $ord['id']
                            ) ?>"
                        >


                        <!-- STATUS -->

                        <div class="form-group">

                            <label>
                                Action
                            </label>

                            <select
                                name="status_update"
                                required
                            >

                                <option
                                    value="Approved / Under Design Task"
                                    <?php
                                    if (
                                        ($ord['status'] ?? '') ===
                                        'Approved / Under Design Task'
                                    ) {
                                        echo 'selected';
                                    }
                                    ?>
                                >
                                    Approve Request ✔
                                </option>


                                <option
                                    value="Declined Spec Requirements"
                                    <?php
                                    if (
                                        ($ord['status'] ?? '') ===
                                        'Declined Spec Requirements'
                                    ) {
                                        echo 'selected';
                                    }
                                    ?>
                                >
                                    Decline Request ✖
                                </option>


                                <option
                                    value="Pending Review"
                                    <?php
                                    if (
                                        ($ord['status'] ?? '') ===
                                        'Pending Review'
                                    ) {
                                        echo 'selected';
                                    }
                                    ?>
                                >
                                    Keep Pending ⏳
                                </option>

                            </select>

                        </div>


                        <!--
                        |--------------------------------------------------------------------------
                        | THIS WAS THE BROKEN LINE
                        |--------------------------------------------------------------------------
                        -->

                        <div class="form-group">

                            <label>
                                Your Response Message
                            </label>

                            <input
                                type="text"
                                name="architect_reply"
                                value="<?= htmlspecialchars(
                                    $ord['architect_reply']
                                    ?? ''
                                ) ?>"
                                placeholder="Andika maoni yako hapa..."
                                required
                            >

                        </div>


                        <!-- SUBMIT -->

                        <button
                            type="submit"
                            class="btn-update"
                        >

                            Submit

                        </button>

                    </form>

                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <p
                style="
                    text-align:center;
                    color:#64748b;
                    padding-top:40px;
                    font-style:italic;
                "
            >

                Hakuna maombi mapya kutoka kwa
                wateja kwa sasa.

            </p>


        <?php endif; ?>

    </div>

</div>


<!--
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
-->

<div class="footer">

    System designed by

    <strong>
        Engineer Misangu Kabizi
    </strong>

    (0754339127)

    at

    <strong>
        MUST
    </strong>

</div>


</body>

</html>

<?php

ob_end_flush();

?>