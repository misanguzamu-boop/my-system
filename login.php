<?php

ob_start();

include 'db.php';


/*
|--------------------------------------------------------------------------
| LOGIN PROCESS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';


    if ($username === "" || $password === "") {

        $error_msg =
            "Tafadhali jaza Username na Password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT * FROM users WHERE username = ?"
        );


        if ($stmt) {

            $stmt->bind_param(
                "s",
                $username
            );

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result && $result->num_rows > 0) {

                $user = $result->fetch_assoc();


                /*
                |--------------------------------------------------------------------------
                | VERIFY PASSWORD
                |--------------------------------------------------------------------------
                */

                if (
                    password_verify(
                        $password,
                        $user['password']
                    )
                ) {

                    $_SESSION['user_id'] =
                        $user['id'];

                    $_SESSION['fullname'] =
                        $user['fullname'];

                    $_SESSION['role'] =
                        $user['role'];


                    /*
                    |--------------------------------------------------------------------------
                    | REDIRECT BY ROLE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $user['role'] === 'architect'
                    ) {

                        header(
                            "Location: architect_dashboard.php"
                        );

                    } else {

                        header(
                            "Location: index.php"
                        );
                    }

                    exit;

                } else {

                    $error_msg =
                        "Username au Password sio sahihi!";
                }

            } else {

                $error_msg =
                    "Username au Password sio sahihi!";
            }


            $stmt->close();

        } else {

            $error_msg =
                "Database error. Tafadhali jaribu tena.";
        }
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
        Login | Shema Shigela Daudi 
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            font-family:
                'Segoe UI',
                sans-serif;

            background: #0f172a;

            color: #f1f5f9;

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;

            margin: 0;

            padding: 20px;
        }


        .box {

            background: #1e293b;

            padding: 40px;

            border-radius: 12px;

            border:
                1px solid #334155;

            width: 100%;

            max-width: 400px;

            text-align: center;

            box-shadow:
                0 20px 50px
                rgba(0, 0, 0, 0.25);
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN TITLE
        |--------------------------------------------------------------------------
        */

        h2 {

            color: #38bdf8;

            margin:
                0 0 8px 0;

            font-size: 28px;
        }


        /*
        |--------------------------------------------------------------------------
        | PROFESSIONAL TITLE
        |--------------------------------------------------------------------------
        */

        .profession {

            color: #a855f7;

            font-size: 14px;

            font-weight: 600;

            line-height: 1.5;

            margin-bottom: 6px;
        }


        /*
        |--------------------------------------------------------------------------
        | BRAND
        |--------------------------------------------------------------------------
        */

        .brand {

            font-size: 11px;

            text-transform: uppercase;

            color: #94a3b8;

            letter-spacing: 2px;

            margin-bottom: 25px;
        }


        /*
        |--------------------------------------------------------------------------
        | INPUTS
        |--------------------------------------------------------------------------
        */

        input {

            width: 100%;

            padding: 12px;

            margin: 10px 0;

            border:
                1px solid #475569;

            border-radius: 6px;

            background: #0f172a;

            color: white;

            outline: none;

            font-size: 14px;
        }


        input:focus {

            border-color: #38bdf8;

            box-shadow:
                0 0 0 2px
                rgba(56, 189, 248, 0.1);
        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN BUTTON
        |--------------------------------------------------------------------------
        */

        .btn {

            width: 100%;

            padding: 12px;

            background: #0284c7;

            color: white;

            border: none;

            border-radius: 6px;

            font-weight: bold;

            cursor: pointer;

            font-size: 16px;

            margin-top: 15px;
        }


        .btn:hover {

            background: #0369a1;
        }


        /*
        |--------------------------------------------------------------------------
        | LINKS
        |--------------------------------------------------------------------------
        */

        a {

            color: #38bdf8;

            text-decoration: none;

            font-size: 14px;

            display: inline-block;

            margin-top: 15px;
        }


        a:hover {

            text-decoration: underline;
        }


        /*
        |--------------------------------------------------------------------------
        | ERROR MESSAGE
        |--------------------------------------------------------------------------
        */

        .err {

            color: #ef4444;

            background: #fee2e2;

            padding: 10px;

            border-radius: 6px;

            font-size: 13px;

            margin-bottom: 15px;
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS MESSAGE
        |--------------------------------------------------------------------------
        */

        .success {

            color: #16a34a;

            background: #dcfce7;

            padding: 10px;

            border-radius: 6px;

            font-size: 13px;

            margin-bottom: 15px;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 500px) {

            .box {

                padding: 30px 22px;
            }

            h2 {

                font-size: 25px;
            }

            .profession {

                font-size: 13px;
            }
        }

    </style>

</head>


<body>


<div class="box">


    <!--
    |--------------------------------------------------------------------------
    | IDENTITY PORTAL
    |--------------------------------------------------------------------------
    -->

    <h2>
        Identity Portal
    </h2>


    <!--
    |--------------------------------------------------------------------------
    | PROFESSIONAL TITLE
    |--------------------------------------------------------------------------
    -->

    <div class="profession">

        Architectural Technologist at
        Mbeya University of Science and Technology

    </div>


    <!--
    |--------------------------------------------------------------------------
    | BRAND NAME
    |--------------------------------------------------------------------------
    -->

    <div class="brand">

        Shema Shigela Daudi 

    </div>


    <!--
    |--------------------------------------------------------------------------
    | SUCCESS / ERROR MESSAGE
    |--------------------------------------------------------------------------
    -->

    <?php

    if (
        isset($_SESSION['success_msg'])
    ):

    ?>

        <div class="success">

            <?= htmlspecialchars(
                $_SESSION['success_msg']
            ) ?>

        </div>

    <?php

        unset(
            $_SESSION['success_msg']
        );

    endif;

    ?>


    <?php if (
        isset($error_msg)
    ): ?>

        <div class="err">

            ⚠️

            <?= htmlspecialchars(
                $error_msg
            ) ?>

        </div>

    <?php endif; ?>


    <!--
    |--------------------------------------------------------------------------
    | LOGIN FORM
    |--------------------------------------------------------------------------
    -->

    <form
        method="POST"
        action=""
    >

        <input
            type="text"
            name="username"
            placeholder="Username"
            autocomplete="username"
            required
        >


        <input
            type="password"
            name="password"
            placeholder="Password"
            autocomplete="current-password"
            required
        >


        <button
            type="submit"
            class="btn"
        >

            Access Terminal

        </button>

    </form>


    <!--
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    -->

    <a href="register.php">

        New client?
        Join Architecture Site Platform

    </a>


</div>


</body>

</html>

<?php

ob_end_flush();

?>