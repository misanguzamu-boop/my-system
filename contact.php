<?php

ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';


/*
|--------------------------------------------------------------------------
| CREATE CONTACT TABLE
|--------------------------------------------------------------------------
*/

$conn->query("
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
| DEFAULT CONTACT
|--------------------------------------------------------------------------
*/

$check = $conn->query(
    "SELECT id FROM admin_contact LIMIT 1"
);

if ($check && $check->num_rows === 0) {

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

        $name =
            "Shema Shigila Daudi";

        $whatsapp =
            "0740220311";

        $phone =
            "0681804661";

        $location =
            "Mbeya, Tanzania";

        $description =
            "Professional architectural design, building plans, custom layouts and architectural consultation services.";

        $stmt->bind_param(
            "sssss",
            $name,
            $whatsapp,
            $phone,
            $location,
            $description
        );

        $stmt->execute();

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| GET CONTACT
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


/*
|--------------------------------------------------------------------------
| WHATSAPP
|--------------------------------------------------------------------------
*/

$whatsappClean =
    preg_replace(
        '/[^0-9]/',
        '',
        $contact['whatsapp']
    );


if (
    substr($whatsappClean, 0, 1) === '0'
) {

    $whatsappClean =
        '255' .
        substr($whatsappClean, 1);
}


$whatsappMessage = urlencode(
    "Hello " .
    $contact['admin_name'] .
    ", I am interested in your architectural services. I would like to discuss my building design project."
);


/*
|--------------------------------------------------------------------------
| PHONE
|--------------------------------------------------------------------------
*/

$phoneClean =
    preg_replace(
        '/[^0-9+]/',
        '',
        $contact['phone']
    );


if (
    substr($phoneClean, 0, 1) === '0'
) {

    $phoneLink =
        '+255' .
        substr($phoneClean, 1);

} else {

    $phoneLink =
        $phoneClean;
}


/*
|--------------------------------------------------------------------------
| MAP
|--------------------------------------------------------------------------
*/

$mapUrl =
    "https://www.google.com/maps/search/?api=1&query="
    . urlencode(
        $contact['location']
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
    Contact | <?= htmlspecialchars($contact['admin_name']) ?>
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background:
        #020617;

    color:
        #cbd5e1;
}

.navbar {

    padding:
        18px 6%;

    background:
        rgba(2,6,23,.95);

    border-bottom:
        1px solid #1e293b;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    position:
        sticky;

    top:
        0;

    z-index:
        100;
}

.brand {

    color:
        white;

    text-decoration:
        none;

    font-size:
        19px;

    font-weight:
        800;
}

.brand span {
    color:
        #38bdf8;
}

.navbar a:not(.brand) {

    color:
        #94a3b8;

    text-decoration:
        none;

    margin-left:
        20px;

    font-size:
        14px;

    font-weight:
        600;
}

.navbar a:hover {
    color:
        #38bdf8;
}

.hero {

    text-align:
        center;

    padding:
        80px 20px 50px;
}

.badge {

    display:
        inline-block;

    padding:
        8px 15px;

    border-radius:
        30px;

    background:
        rgba(14,165,233,.10);

    color:
        #38bdf8;

    border:
        1px solid rgba(56,189,248,.25);

    font-size:
        12px;

    font-weight:
        bold;

    text-transform:
        uppercase;

    letter-spacing:
        1px;
}

.hero h1 {

    color:
        #f8fafc;

    font-size:
        clamp(35px,6vw,62px);

    margin:
        20px 0 15px;
}

.hero h1 span {
    color:
        #38bdf8;
}

.hero p {

    max-width:
        700px;

    margin:
        auto;

    line-height:
        1.8;

    color:
        #94a3b8;
}

.container {

    width:
        min(1100px,92%);

    margin:
        auto;

    padding-bottom:
        70px;
}

.grid {

    display:
        grid;

    grid-template-columns:
        1fr 1.4fr;

    gap:
        25px;
}

.card {

    background:
        #0f172a;

    border:
        1px solid #334155;

    border-radius:
        18px;

    padding:
        30px;

    box-shadow:
        0 20px 50px rgba(0,0,0,.2);
}

.profile-icon {

    width:
        75px;

    height:
        75px;

    border-radius:
        18px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        linear-gradient(
            135deg,
            #0284c7,
            #7c3aed
        );

    font-size:
        32px;

    margin-bottom:
        20px;
}

.card h2 {

    color:
        #f8fafc;

    margin:
        0 0 8px;
}

.role {

    color:
        #38bdf8;

    font-size:
        12px;

    font-weight:
        bold;

    text-transform:
        uppercase;

    letter-spacing:
        1px;

    margin-bottom:
        20px;
}

.description {

    color:
        #94a3b8;

    line-height:
        1.8;

    font-size:
        14px;

    margin-bottom:
        25px;
}

.contact-item {

    padding:
        15px;

    margin-bottom:
        12px;

    border:
        1px solid #334155;

    border-radius:
        11px;

    background:
        #111827;
}

.contact-label {

    display:
        block;

    color:
        #64748b;

    font-size:
        11px;

    text-transform:
        uppercase;

    margin-bottom:
        5px;
}

.contact-value {

    color:
        #f8fafc;

    font-weight:
        bold;
}

.buttons {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        12px;

    margin-top:
        20px;
}

.btn {

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        13px;

    border-radius:
        9px;

    color:
        white;

    text-decoration:
        none;

    font-weight:
        bold;

    font-size:
        13px;
}

.whatsapp {
    background:
        #16a34a;
}

.call {
    background:
        #0284c7;
}

.map {

    margin-top:
        20px;

    padding:
        20px;

    background:
        #111827;

    border:
        1px solid #334155;

    border-radius:
        13px;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        15px;
}

.map strong {

    display:
        block;

    color:
        white;

    margin-bottom:
        5px;
}

.map span {

    color:
        #64748b;

    font-size:
        13px;
}

.map-btn {

    background:
        #7c3aed;

    color:
        white;

    text-decoration:
        none;

    padding:
        10px 15px;

    border-radius:
        8px;

    font-size:
        12px;

    font-weight:
        bold;

    white-space:
        nowrap;
}

.services {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        14px;

    margin-top:
        20px;
}

.service {

    background:
        #111827;

    border:
        1px solid #334155;

    border-radius:
        12px;

    padding:
        20px;
}

.service-icon {
    font-size:
        25px;
}

.service h3 {

    color:
        #f8fafc;

    font-size:
        15px;
}

.service p {

    color:
        #64748b;

    font-size:
        12px;

    line-height:
        1.6;
}

.cta {

    margin-top:
        25px;

    padding:
        35px;

    text-align:
        center;

    border-radius:
        18px;

    background:
        linear-gradient(
            135deg,
            #0c4a6e,
            #312e81
        );
}

.cta h2 {
    color:
        white;
}

.cta p {

    max-width:
        650px;

    margin:
        auto auto 20px;

    line-height:
        1.7;
}

.cta a {

    display:
        inline-block;

    background:
        white;

    color:
        #0f172a;

    text-decoration:
        none;

    padding:
        13px 22px;

    border-radius:
        9px;

    font-weight:
        bold;
}

footer {

    text-align:
        center;

    padding:
        30px;

    border-top:
        1px solid #1e293b;

    color:
        #64748b;

    font-size:
        12px;
}

@media(max-width:800px) {

    .grid {
        grid-template-columns:
            1fr;
    }

}

@media(max-width:550px) {

    .navbar {

        flex-direction:
            column;

        gap:
            15px;
    }

    .navbar a:not(.brand) {

        margin-left:
            8px;

        margin-right:
            8px;
    }

    .buttons,
    .services {
        grid-template-columns:
            1fr;
    }

    .map {

        flex-direction:
            column;

        align-items:
            flex-start;
    }

    .map-btn {

        width:
            100%;

        text-align:
            center;
    }

}

</style>

</head>

<body>


<nav class="navbar">

    <a
        href="index.php"
        class="brand"
    >
        ARCHI<span>PORTFOLIO</span>
    </a>

    <div>

        <a href="index.php">
            Home
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <?php if (
                isset($_SESSION['role']) &&
                $_SESSION['role'] === 'architect'
            ): ?>

                <a href="architect_dashboard.php">
                    Dashboard
                </a>

            <?php endif; ?>

            <a href="logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="login.php">
                Login
            </a>

        <?php endif; ?>

    </div>

</nav>


<header class="hero">

    <div class="badge">
        Professional Architectural Services
    </div>

    <h1>
        Let's Build Your
        <span>Dream Space.</span>
    </h1>

    <p>
        Wasiliana na architect kwa building plans,
        architectural designs, custom layouts na
        ushauri wa kitaalamu kuhusu project yako.
    </p>

</header>


<main class="container">

    <div class="grid">


        <section class="card">

            <div class="profile-icon">
                🏗️
            </div>

            <h2>
                <?= htmlspecialchars($contact['admin_name']) ?>
            </h2>

            <div class="role">
                Architect & Building Design Specialist
            </div>

            <p class="description">
                <?= nl2br(
                    htmlspecialchars(
                        $contact['description']
                    )
                ) ?>
            </p>


            <div class="contact-item">

                <span class="contact-label">
                    WhatsApp
                </span>

                <span class="contact-value">
                    <?= htmlspecialchars($contact['whatsapp']) ?>
                </span>

            </div>


            <div class="contact-item">

                <span class="contact-label">
                    Phone
                </span>

                <span class="contact-value">
                    <?= htmlspecialchars($contact['phone']) ?>
                </span>

            </div>


            <div class="contact-item">

                <span class="contact-label">
                    Location
                </span>

                <span class="contact-value">
                    <?= htmlspecialchars($contact['location']) ?>
                </span>

            </div>


            <div class="buttons">

                <a
                    href="https://wa.me/<?= htmlspecialchars($whatsappClean) ?>?text=<?= $whatsappMessage ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn whatsapp"
                >
                    💬 WhatsApp
                </a>

                <a
                    href="tel:<?= htmlspecialchars($phoneLink) ?>"
                    class="btn call"
                >
                    📞 Call Now
                </a>

            </div>

        </section>


        <section class="card">

            <h2>
                What Can We Design?
            </h2>

            <p class="description">
                Chagua aina ya kazi inayokufaa,
                kisha wasiliana na architect kwa
                maelezo zaidi.
            </p>


            <div class="services">

                <div class="service">

                    <div class="service-icon">
                        🏠
                    </div>

                    <h3>
                        Residential Buildings
                    </h3>

                    <p>
                        House plans na architectural
                        designs za nyumba za makazi.
                    </p>

                </div>


                <div class="service">

                    <div class="service-icon">
                        🏢
                    </div>

                    <h3>
                        Commercial Buildings
                    </h3>

                    <p>
                        Offices, shops, apartments
                        na commercial projects.
                    </p>

                </div>


                <div class="service">

                    <div class="service-icon">
                        📐
                    </div>

                    <h3>
                        Building Plans
                    </h3>

                    <p>
                        Professional building plans
                        kulingana na project yako.
                    </p>

                </div>


                <div class="service">

                    <div class="service-icon">
                        🧱
                    </div>

                    <h3>
                        Custom Layouts
                    </h3>

                    <p>
                        Customized layouts kulingana
                        na plot size na mahitaji yako.
                    </p>

                </div>


                <div class="service">

                    <div class="service-icon">
                        🔄
                    </div>

                    <h3>
                        Design Modification
                    </h3>

                    <p>
                        Kuboresha au kurekebisha
                        existing architectural designs.
                    </p>

                </div>


                <div class="service">

                    <div class="service-icon">
                        💡
                    </div>

                    <h3>
                        Consultation
                    </h3>

                    <p>
                        Ushauri wa kitaalamu kuhusu
                        architectural project planning.
                    </p>

                </div>

            </div>


            <div class="map">

                <div>

                    <strong>
                        📍 Our Location
                    </strong>

                    <span>
                        <?= htmlspecialchars($contact['location']) ?>
                    </span>

                </div>

                <a
                    href="<?= htmlspecialchars($mapUrl) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="map-btn"
                >
                    View Location
                </a>

            </div>

        </section>

    </div>


    <section class="cta">

        <h2>
            Have a Project in Mind?
        </h2>

        <p>
            Wasiliana moja kwa moja na
            <?= htmlspecialchars($contact['admin_name']) ?>
            kupitia WhatsApp ili kujadili
            mahitaji ya project yako.
        </p>

        <a
            href="https://wa.me/<?= htmlspecialchars($whatsappClean) ?>?text=<?= $whatsappMessage ?>"
            target="_blank"
            rel="noopener noreferrer"
        >
            💬 Start WhatsApp Conversation
        </a>

    </section>

</main>


<footer>

    © <?= date("Y") ?>

    <?= htmlspecialchars($contact['admin_name']) ?>

    — Architectural Portfolio

    <br>

    <?= htmlspecialchars($contact['location']) ?>

</footer>


</body>

</html>