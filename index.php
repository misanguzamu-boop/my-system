<?php
ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

/*
|--------------------------------------------------------------------------
| FOLLOW / JOIN WEBSITE
|--------------------------------------------------------------------------
*/
if (isset($_GET['follow_action']) && isset($_SESSION['user_id'])) {

    $uid = intval($_SESSION['user_id']);

    $stmt = $conn->prepare(
        "INSERT IGNORE INTO followers (user_id) VALUES (?)"
    );

    if ($stmt) {
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| ORDER BLUEPRINT
|--------------------------------------------------------------------------
*/
$order_success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_order'])) {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $customer_id = intval($_SESSION['user_id']);
    $design_id   = intval($_POST['design_id'] ?? 0);
    $plot_size   = trim($_POST['plot_size'] ?? "");
    $custom_notes = trim($_POST['custom_notes'] ?? "");

    if ($design_id > 0 && $plot_size !== "") {

        $stmt = $conn->prepare(
            "INSERT INTO orders
            (customer_id, design_id, plot_size, custom_notes)
            VALUES (?, ?, ?, ?)"
        );

        if ($stmt) {

            $stmt->bind_param(
                "iiss",
                $customer_id,
                $design_id,
                $plot_size,
                $custom_notes
            );

            if ($stmt->execute()) {

                $order_success =
                    "Oda yako imepokelewa kwa mafanikio! Architect Shema ataifanyia kazi hivi punde.";

            } else {

                $order_success =
                    "Samahani, oda yako haikufanikiwa kutumwa.";
            }

            $stmt->close();

        } else {

            $order_success =
                "Database error: Oda haikuweza kutumwa.";
        }

    } else {

        $order_success =
            "Tafadhali jaza plot size na uchague design.";
    }
}

/*
|--------------------------------------------------------------------------
| GET DESIGNS
|--------------------------------------------------------------------------
*/
$designs_res = $conn->query(
    "SELECT * FROM designs ORDER BY id DESC"
);

/*
|--------------------------------------------------------------------------
| TOTAL FOLLOWERS
|--------------------------------------------------------------------------
*/
$total_followers = 0;

$followers_query = $conn->query(
    "SELECT COUNT(*) AS count FROM followers"
);

if ($followers_query) {

    $followers_data = $followers_query->fetch_assoc();

    $total_followers = intval(
        $followers_data['count'] ?? 0
    );
}

/*
|--------------------------------------------------------------------------
| CHECK IF USER IS FOLLOWING
|--------------------------------------------------------------------------
*/
$is_following = false;

if (isset($_SESSION['user_id'])) {

    $uid = intval($_SESSION['user_id']);

    $stmt = $conn->prepare(
        "SELECT id FROM followers WHERE user_id = ? LIMIT 1"
    );

    if ($stmt) {

        $stmt->bind_param("i", $uid);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $is_following = true;
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Shema Shingela Daudi | Architectural Portfolio
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family:
                "Segoe UI",
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;

            background: #0f172a;

            color: #cbd5e1;

            margin: 0;

            padding: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | HERO
        |--------------------------------------------------------------------------
        */

        .hero {
            background:
                linear-gradient(
                    135deg,
                    #1e293b 0%,
                    #0f172a 100%
                );

            border-bottom:
                1px solid #334155;

            padding: 90px 30px 60px;

            text-align: center;

            position: relative;
        }

        .hero h1 {

            margin: 0;

            font-size: 36px;

            color: #f8fafc;

            font-weight: 800;

            letter-spacing: -1px;
        }

        .hero p {

            margin: 10px 0 20px;

            font-size: 16px;

            color: #94a3b8;
        }

        /*
        |--------------------------------------------------------------------------
        | NAVIGATION
        |--------------------------------------------------------------------------
        */

        .nav-links {

            position: absolute;

            top: 20px;

            right: 30px;

            display: flex;

            align-items: center;

            gap: 20px;

            flex-wrap: wrap;

            justify-content: center;
        }

        .nav-links a {

            color: #38bdf8;

            text-decoration: none;

            font-weight: 600;

            font-size: 14px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        /*
        |--------------------------------------------------------------------------
        | FOLLOW BUTTON
        |--------------------------------------------------------------------------
        */

        .btn-follow {

            padding: 10px 24px;

            background: #0284c7;

            color: white;

            border: none;

            border-radius: 20px;

            font-weight: bold;

            cursor: pointer;

            text-decoration: none;

            display: inline-block;
        }

        .btn-follow:hover {
            background: #0369a1;
        }

        .btn-follow.active {

            background: #334155;

            color: #94a3b8;

            cursor: default;
        }

        /*
        |--------------------------------------------------------------------------
        | CONTAINER
        |--------------------------------------------------------------------------
        */

        .container {

            width: 100%;

            margin: 0 auto;
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS MESSAGE
        |--------------------------------------------------------------------------
        */

        .success-message {

            background: #15803d;

            color: white;

            padding: 15px;

            text-align: center;

            font-weight: bold;

            max-width: 1220px;

            margin: 20px auto;

            border-radius: 8px;

            width: calc(100% - 40px);
        }

        /*
        |--------------------------------------------------------------------------
        | SECTION TITLE
        |--------------------------------------------------------------------------
        */

        .section-title {

            text-align: center;

            margin: 40px 0 20px;

            font-size: 24px;

            color: #f8fafc;

            font-weight: 700;
        }

        /*
        |--------------------------------------------------------------------------
        | PORTFOLIO GRID
        |--------------------------------------------------------------------------
        */

        .portfolio-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(320px, 1fr)
                );

            gap: 30px;

            padding:
                0 40px 50px;

            max-width: 1300px;

            margin: 0 auto;
        }

        /*
        |--------------------------------------------------------------------------
        | DESIGN CARD
        |--------------------------------------------------------------------------
        */

        .design-card {

            background: #1e293b;

            border-radius: 12px;

            border:
                1px solid #334155;

            overflow: hidden;

            display: flex;

            flex-direction: column;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .design-card:hover {

            transform:
                translateY(-5px);

            box-shadow:
                0 15px 30px
                rgba(0, 0, 0, 0.25);
        }

        /*
        |--------------------------------------------------------------------------
        | DESIGN IMAGE
        |--------------------------------------------------------------------------
        */

        .design-img {

            width: 100%;

            height: 220px;

            object-fit: cover;

            background: #334155;

            display: block;
        }

        /*
        |--------------------------------------------------------------------------
        | DESIGN INFORMATION
        |--------------------------------------------------------------------------
        */

        .design-info {

            padding: 20px;

            flex: 1;

            display: flex;

            flex-direction: column;
        }

        .design-title {

            font-size: 18px;

            font-weight: bold;

            color: #f8fafc;

            margin:
                0 0 5px;
        }

        .design-cat {

            font-size: 12px;

            text-transform: uppercase;

            color: #38bdf8;

            font-weight: bold;

            letter-spacing: 1px;
        }

        .design-desc {

            font-size: 14px;

            color: #94a3b8;

            margin: 10px 0;

            line-height: 1.5;

            flex: 1;
        }

        .design-cost {

            font-size: 15px;

            color: #10b981;

            font-weight: bold;

            margin-bottom: 15px;
        }

        /*
        |--------------------------------------------------------------------------
        | ORDER BOX
        |--------------------------------------------------------------------------
        */

        .order-box {

            border-top:
                1px solid #334155;

            padding-top: 15px;

            margin-top: 10px;
        }

        .order-box label {

            display: block;

            color: #cbd5e1;

            font-size: 13px;

            font-weight: 600;

            margin-top: 8px;
        }

        input,
        textarea {

            width: 100%;

            padding: 10px;

            margin: 6px 0;

            background: #0f172a;

            color: white;

            border:
                1px solid #475569;

            border-radius: 5px;

            box-sizing: border-box;

            font-size: 13px;

            outline: none;
        }

        input:focus,
        textarea:focus {

            border-color: #38bdf8;
        }

        textarea {

            resize: vertical;

            min-height: 80px;
        }

        .btn-order {

            width: 100%;

            padding: 11px;

            margin-top: 8px;

            background: #16a34a;

            color: white;

            border: none;

            border-radius: 6px;

            font-weight: bold;

            cursor: pointer;
        }

        .btn-order:hover {
            background: #15803d;
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENT ORDERS
        |--------------------------------------------------------------------------
        */

        .client-ledger-section {

            max-width: 1220px;

            margin:
                0 auto 5px;

            padding:
                0 40px;
        }

        .ledger-box {

            background: #1e293b;

            border:
                1px solid #334155;

            border-radius: 8px;

            padding: 20px;

            margin-bottom: 40px;

            overflow-x: auto;
        }

        .ledger-box h3 {

            margin-top: 0;

            color: #38bdf8;
        }

        table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 15px;

            font-size: 14px;

            min-width: 700px;
        }

        th,
        td {

            padding: 12px;

            text-align: left;

            border-bottom:
                1px solid #334155;
        }

        th {

            background: #0f172a;

            color: #94a3b8;
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        .status-badge {

            padding:
                4px 8px;

            border-radius: 4px;

            font-weight: bold;

            font-size: 12px;

            display: inline-block;
        }

        .status-badge.pending {

            background: #b45309;

            color: #fef3c7;
        }

        .status-badge.approved {

            background: #15803d;

            color: #dcfce7;
        }

        .status-badge.declined {

            background: #991b1b;

            color: #fee2e2;
        }

        /*
        |--------------------------------------------------------------------------
        | NO DESIGNS
        |--------------------------------------------------------------------------
        */

        .no-designs {

            grid-column: 1 / -1;

            text-align: center;

            background: #1e293b;

            border:
                1px solid #334155;

            padding: 40px;

            border-radius: 10px;

            color: #94a3b8;
        }

        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        footer {

            border-top:
                1px solid #334155;

            background: #020617;

            text-align: center;

            padding: 25px;

            color: #64748b;

            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .hero {

                padding:
                    110px 20px 50px;
            }

            .hero h1 {

                font-size: 28px;
            }

            .nav-links {

                position: static;

                margin-bottom: 30px;
            }

            .portfolio-grid {

                grid-template-columns: 1fr;

                padding:
                    0 20px 40px;
            }

            .client-ledger-section {

                padding:
                    0 20px;
            }

            .section-title {

                font-size: 20px;

                padding: 0 20px;
            }
        }

    </style>

</head>

<body>

<!--
|--------------------------------------------------------------------------
| HERO SECTION
|--------------------------------------------------------------------------
-->

<div class="hero">

    <div class="nav-links">

        <a href="index.php">
            Showcase Home
        </a>
        <a href="contact.php">
    📞 Contact Admin
</a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <?php if (
                isset($_SESSION['role']) &&
                $_SESSION['role'] === 'architect'
            ): ?>

                <a
                    href="architect_dashboard.php"
                    style="
                        color:#a855f7;
                        font-weight:bold;
                    "
                >
                    🛠️ Architect Panel Workspace
                </a>

            <?php endif; ?>

            <a
                href="logout.php"
                style="color:#ef4444;"
            >
                Sign Out
            </a>

        <?php else: ?>

            <a href="login.php">
                Portal Sign In
            </a>

            <a href="register.php">
                Sign Up
            </a>

        <?php endif; ?>

    </div>


    <h1>
        SHEMA SHINGELA DAUDI
    </h1>

    <p>
        Professional Architect Portfolio Showcase
        & Custom Building Layout Design Studio
    </p>


    <div>

        <span
            style="
                margin-right:15px;
                color:#94a3b8;
            "
        >
            ✨ Followers:

            <strong>
                <?= $total_followers ?>
            </strong>
        </span>


        <?php if (isset($_SESSION['user_id'])): ?>

            <?php if ($is_following): ?>

                <button
                    class="btn-follow active"
                    type="button"
                >
                    ✓ Following Website
                </button>

            <?php else: ?>

                <a
                    href="?follow_action=true"
                    class="btn-follow"
                >
                    Join Studio & Follow
                </a>

            <?php endif; ?>

        <?php else: ?>

            <a
                href="login.php"
                class="btn-follow"
            >
                Login to Follow Website
            </a>

        <?php endif; ?>

    </div>

</div>


<div class="container">

    <!--
    |--------------------------------------------------------------------------
    | ORDER SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    -->

    <?php if (!empty($order_success)): ?>

        <div class="success-message">

            <?= htmlspecialchars($order_success) ?>

        </div>

    <?php endif; ?>


    <!--
    |--------------------------------------------------------------------------
    | CUSTOMER ORDER TRACKING
    |--------------------------------------------------------------------------
    -->

    <?php

    if (
        isset($_SESSION['user_id']) &&
        isset($_SESSION['role']) &&
        $_SESSION['role'] === 'customer'
    ):

        $customer_id =
            intval($_SESSION['user_id']);

        $my_orders = $conn->query(
            "SELECT
                o.*,
                d.title
             FROM orders o
             JOIN designs d
                ON o.design_id = d.id
             WHERE o.customer_id = $customer_id
             ORDER BY o.id DESC"
        );

    ?>

        <div class="client-ledger-section">

            <div class="ledger-box">

                <h3>
                    Your Layout Orders & Status Tracking
                </h3>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Design Requested
                            </th>

                            <th>
                                Plot Size
                            </th>

                            <th>
                                Status Verification
                            </th>

                            <th>
                                Architect Discussion / Feedbacks
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (
                        $my_orders &&
                        $my_orders->num_rows > 0
                    ): ?>

                        <?php while (
                            $mo = $my_orders->fetch_assoc()
                        ): ?>

                            <?php

                            $status =
                                $mo['status']
                                ?? 'Pending';

                            $class =
                                'pending';

                            if (
                                $status ===
                                'Approved / Under Design Task'
                            ) {

                                $class =
                                    'approved';
                            }

                            if (
                                $status ===
                                'Declined Spec Requirements'
                            ) {

                                $class =
                                    'declined';
                            }

                            ?>

                            <tr>

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $mo['title']
                                            ?? 'Unknown Design'
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $mo['plot_size']
                                        ?? ''
                                    ) ?>

                                </td>


                                <td>

                                    <span
                                        class="status-badge <?= $class ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $status
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <i
                                        style="
                                            color:#94a3b8;
                                        "
                                    >

                                        <?php if (
                                            !empty(
                                                $mo['architect_reply']
                                            )
                                        ): ?>

                                            <?= htmlspecialchars(
                                                $mo['architect_reply']
                                            ) ?>

                                        <?php else: ?>

                                            Awaiting analysis...

                                        <?php endif; ?>

                                    </i>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="4"
                                style="
                                    text-align:center;
                                    color:#64748b;
                                "
                            >

                                Hujafanya oda ya ramani yoyote
                                kwa sasa.
                                Chagua mjengo hapo chini!

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php endif; ?>


    <!--
    |--------------------------------------------------------------------------
    | PORTFOLIO TITLE
    |--------------------------------------------------------------------------
    -->

    <div class="section-title">

        Architectural Layout Gallery
        Showcase Collection

    </div>


    <!--
    |--------------------------------------------------------------------------
    | DESIGNS
    |--------------------------------------------------------------------------
    -->

    <div class="portfolio-grid">

        <?php if (
            $designs_res &&
            $designs_res->num_rows > 0
        ): ?>

            <?php while (
                $row = $designs_res->fetch_assoc()
            ): ?>

                <div class="design-card">


                    <!-- DESIGN IMAGE -->

                    <?php if (
                        !empty($row['image_path'])
                    ): ?>

                        <img
                            src="<?= htmlspecialchars(
                                $row['image_path']
                            ) ?>"
                            class="design-img"
                            alt="<?= htmlspecialchars(
                                $row['title']
                                ?? 'Architectural Design'
                            ) ?>"
                        >

                    <?php else: ?>

                        <div
                            class="design-img"
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                color:#64748b;
                                font-size:13px;
                            "
                        >

                            No Visual Matrix Rendered

                        </div>

                    <?php endif; ?>


                    <!-- DESIGN INFORMATION -->

                    <div class="design-info">


                        <div class="design-cat">

                            <?= htmlspecialchars(
                                $row['category']
                                ?? 'Architectural Design'
                            ) ?>

                        </div>


                        <h3 class="design-title">

                            <?= htmlspecialchars(
                                $row['title']
                                ?? 'Untitled Design'
                            ) ?>

                        </h3>


                        <div class="design-desc">

                            <?= nl2br(
                                htmlspecialchars(
                                    $row['description']
                                    ?? 'Professional architectural building layout.'
                                )
                            ) ?>

                        </div>


                        <?php if (
                            isset($row['price']) &&
                            $row['price'] !== ''
                        ): ?>

                            <div class="design-cost">

                                Estimated Cost:
                                <?= htmlspecialchars(
                                    $row['price']
                                ) ?>

                            </div>

                        <?php endif; ?>


                        <!--
                        -------------------------------------------------------
                        | ORDER FORM
                        -------------------------------------------------------
                        -->

                        <?php if (
                            isset($_SESSION['user_id']) &&
                            isset($_SESSION['role']) &&
                            $_SESSION['role'] === 'customer'
                        ): ?>

                            <div class="order-box">

                                <form
                                    method="POST"
                                    action="index.php"
                                >

                                    <input
                                        type="hidden"
                                        name="design_id"
                                        value="<?= intval(
                                            $row['id']
                                        ) ?>"
                                    >


                                    <label>
                                        Plot Size
                                    </label>

                                    <input
                                        type="text"
                                        name="plot_size"
                                        placeholder="Mfano: 20m x 30m"
                                        required
                                    >


                                    <label>
                                        Custom Requirements
                                    </label>

                                    <textarea
                                        name="custom_notes"
                                        placeholder="Eleza mahitaji yako ya ramani..."
                                    ></textarea>


                                    <button
                                        type="submit"
                                        name="submit_order"
                                        class="btn-order"
                                    >

                                        📐 Order This Blueprint

                                    </button>

                                </form>

                            </div>

                        <?php else: ?>

                            <div class="order-box">

                                <a
                                    href="login.php"
                                    class="btn-order"
                                    style="
                                        display:block;
                                        text-align:center;
                                        text-decoration:none;
                                    "
                                >

                                    🔐 Login to Order Blueprint

                                </a>

                            </div>

                        <?php endif; ?>


                    </div>

                </div>

            <?php endwhile; ?>


        <?php else: ?>

            <div class="no-designs">

                <h3>
                    No Architectural Designs Available
                </h3>

                <p>
                    Architect bado haja-upload
                    architectural designs.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>


<!--
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
-->

<footer>

    © <?= date('Y') ?>
    Shema Shingela Daudi —
    Professional Architectural Portfolio

</footer>


</body>

</html>

<?php
ob_end_flush();
?>