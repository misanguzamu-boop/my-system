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


$success = "";
$error = "";


/*
|--------------------------------------------------------------------------
| DELETE DESIGN
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['delete_design'])
) {

    $design_id =
        intval($_POST['design_id'] ?? 0);


    if ($design_id <= 0) {

        $error =
            "Design haikutambuliwa.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | GET DESIGN
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT
                id,
                title,
                image_path
            FROM designs
            WHERE id = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $error =
                "Database error wakati wa kutafuta design.";

        } else {

            $stmt->bind_param(
                "i",
                $design_id
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            $design =
                $result
                ? $result->fetch_assoc()
                : null;

            $stmt->close();


            if (!$design) {

                $error =
                    "Design hiyo haipo.";

            } else {


                /*
                |--------------------------------------------------------------------------
                | CHECK CUSTOMER ORDERS
                |--------------------------------------------------------------------------
                */

                $orderStmt = $conn->prepare("
                    SELECT COUNT(*) AS total
                    FROM orders
                    WHERE design_id = ?
                ");

                $orderCount = 0;

                if ($orderStmt) {

                    $orderStmt->bind_param(
                        "i",
                        $design_id
                    );

                    $orderStmt->execute();

                    $orderResult =
                        $orderStmt->get_result();

                    if ($orderResult) {

                        $orderData =
                            $orderResult->fetch_assoc();

                        $orderCount =
                            intval(
                                $orderData['total'] ?? 0
                            );
                    }

                    $orderStmt->close();
                }


                /*
                |--------------------------------------------------------------------------
                | DO NOT DELETE DESIGN WITH ORDERS
                |--------------------------------------------------------------------------
                */

                if ($orderCount > 0) {

                    $error =
                        "Design hii haiwezi kufutwa kwa sasa kwa sababu ina "
                        . $orderCount
                        . " customer order zinazohusiana nayo.";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | DELETE DATABASE RECORD
                    |--------------------------------------------------------------------------
                    */

                    $deleteStmt = $conn->prepare("
                        DELETE FROM designs
                        WHERE id = ?
                    ");


                    if ($deleteStmt) {

                        $deleteStmt->bind_param(
                            "i",
                            $design_id
                        );


                        if ($deleteStmt->execute()) {


                            /*
                            |--------------------------------------------------------------------------
                            | DELETE IMAGE FROM SERVER
                            |--------------------------------------------------------------------------
                            */

                            $imagePath =
                                $design['image_path'] ?? '';


                            if (
                                $imagePath !== "" &&
                                file_exists($imagePath)
                            ) {

                                @unlink($imagePath);
                            }


                            $success =
                                "✓ Design \""
                                . $design['title']
                                . "\" imefutwa kwa mafanikio.";

                        } else {

                            $error =
                                "Design haikuweza kufutwa.";
                        }


                        $deleteStmt->close();

                    } else {

                        $error =
                            "Database error wakati wa kufuta design.";
                    }
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET ALL DESIGNS
|--------------------------------------------------------------------------
*/

$designs = [];

$result = $conn->query("
    SELECT
        id,
        title,
        category,
        description,
        image_path,
        estimated_cost
    FROM designs
    ORDER BY id DESC
");

if ($result) {

    while (
        $row = $result->fetch_assoc()
    ) {

        $designs[] = $row;
    }
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
    Delete Designs | Architect
</title>


<style>

* {
    box-sizing:
        border-box;
}

body {

    margin:
        0;

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

    margin:
        0;

    color:
        #f87171;

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
        min(1200px,94%);

    margin:
        35px auto;
}

.info {

    background:
        #1e293b;

    border:
        1px solid #334155;

    padding:
        20px;

    border-radius:
        12px;

    margin-bottom:
        25px;

    color:
        #94a3b8;

    line-height:
        1.7;
}

.message {

    padding:
        14px;

    border-radius:
        8px;

    margin-bottom:
        20px;

    font-weight:
        bold;
}

.success {

    background:
        #14532d;

    color:
        #dcfce7;
}

.error {

    background:
        #7f1d1d;

    color:
        #fee2e2;
}

.design-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            auto-fill,
            minmax(280px,1fr)
        );

    gap:
        20px;
}

.design-card {

    background:
        #1e293b;

    border:
        1px solid #334155;

    border-radius:
        14px;

    overflow:
        hidden;

    display:
        flex;

    flex-direction:
        column;
}

.design-image {

    width:
        100%;

    height:
        200px;

    object-fit:
        cover;

    background:
        #020617;
}

.design-body {

    padding:
        20px;

    flex:
        1;

    display:
        flex;

    flex-direction:
        column;
}

.design-body h2 {

    color:
        #f8fafc;

    font-size:
        18px;

    margin:
        0 0 7px;
}

.category {

    color:
        #38bdf8;

    font-size:
        11px;

    text-transform:
        uppercase;

    font-weight:
        bold;

    letter-spacing:
        .8px;
}

.description {

    color:
        #94a3b8;

    font-size:
        13px;

    line-height:
        1.6;

    margin:
        12px 0;

    flex:
        1;
}

.cost {

    color:
        #34d399;

    font-weight:
        bold;

    font-size:
        14px;

    margin-bottom:
        15px;
}

.delete-form {

    margin-top:
        auto;
}

.delete-btn {

    width:
        100%;

    padding:
        11px;

    border:
        none;

    border-radius:
        7px;

    background:
        #dc2626;

    color:
        white;

    font-weight:
        bold;

    cursor:
        pointer;
}

.delete-btn:hover {

    background:
        #b91c1c;
}

.empty {

    background:
        #1e293b;

    border:
        1px solid #334155;

    border-radius:
        12px;

    padding:
        40px;

    text-align:
        center;

    color:
        #94a3b8;
}

@media(max-width:650px) {

    .header {

        flex-direction:
            column;

        align-items:
            flex-start;
    }

    .container {

        width:
            calc(100% - 25px);
    }

}

</style>

</head>

<body>


<div class="header">

    <h1>
        🗑️ DELETE PORTFOLIO DESIGNS
    </h1>

    <div>

        <a href="architect_dashboard.php">
            ← Dashboard
        </a>

        &nbsp;&nbsp;

        <a href="index.php">
            Public Home
        </a>

    </div>

</div>


<div class="container">


    <div class="info">

        <strong style="color:#f8fafc;">
            Manage Portfolio Designs
        </strong>

        <br>

        Hapa unaweza kufuta architectural
        projects ambazo huzihitaji tena.

        <br>

        <span style="color:#f87171;">
            ⚠️ Design yenye customer order haiwezi
            kufutwa ili kulinda historia ya customer.
        </span>

    </div>


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


    <?php if (count($designs) > 0): ?>


        <div class="design-grid">


            <?php foreach ($designs as $design): ?>


                <div class="design-card">


                    <?php

                    $image =
                        $design['image_path']
                        ?? '';

                    if (
                        $image !== "" &&
                        file_exists($image)
                    ) {

                        $imageUrl =
                            $image;

                    } else {

                        $imageUrl =
                            "";
                    }

                    ?>


                    <?php if ($imageUrl !== ""): ?>

                        <img
                            src="<?= htmlspecialchars($imageUrl) ?>"
                            class="design-image"
                            alt="<?= htmlspecialchars($design['title']) ?>"
                        >

                    <?php else: ?>

                        <div
                            class="design-image"
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                color:#64748b;
                            "
                        >
                            No Image
                        </div>

                    <?php endif; ?>


                    <div class="design-body">


                        <div class="category">

                            <?= htmlspecialchars(
                                $design['category']
                            ) ?>

                        </div>


                        <h2>

                            <?= htmlspecialchars(
                                $design['title']
                            ) ?>

                        </h2>


                        <div class="description">

                            <?= htmlspecialchars(
                                $design['description']
                            ) ?>

                        </div>


                        <div class="cost">

                            <?= htmlspecialchars(
                                $design['estimated_cost']
                            ) ?>

                        </div>


                        <form
                            method="POST"
                            class="delete-form"
                            onsubmit="
                                return confirm(
                                    'Una uhakika unataka kufuta design hii? Hatua hii haiwezi kurudishwa.'
                                );
                            "
                        >

                            <input
                                type="hidden"
                                name="design_id"
                                value="<?= intval($design['id']) ?>"
                            >

                            <input
                                type="hidden"
                                name="delete_design"
                                value="1"
                            >

                            <button
                                type="submit"
                                class="delete-btn"
                            >

                                🗑️ Delete This Design

                            </button>

                        </form>


                    </div>

                </div>


            <?php endforeach; ?>


        </div>


    <?php else: ?>


        <div class="empty">

            📐 Hakuna architectural designs
            zilizopo kwenye portfolio kwa sasa.

        </div>


    <?php endif; ?>


</div>

</body>

</html>