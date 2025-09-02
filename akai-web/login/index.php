<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>
    <style>
        /* Color Palette */
        :root {
            --dark-bg: #121212;
            --bg-darker: #222222;
            --text-light: #e0e0e0;
            --accent-warm: #ff6f61;
            --placeholder-muted: #ffffff;
        }

        /* Reset & base */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            height: 100vh;
            background: var(--dark-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Container */
        .login-container {
            background: var(--deep-teal);
            padding: 3rem 3.5rem;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.7);
            width: 320px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Heading */
        .login-container h2 {
            margin-bottom: 2rem;
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: 0.05em;
            color: var(--accent-warm);
            user-select: none;
        }

        /* Inputs */
        .input-group {
            position: relative;
            margin-bottom: 2rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            background: var(--dark-bg);
            color: var(--text-light);
            transition: box-shadow 0.3s ease, background-color 0.3s ease;
            outline-offset: 4px;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: var(--earth-brown);
            font-weight: 600;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            background: var(--accent-warm);
            color: var(--deep-teal);
            box-shadow: 0 0 8px var(--accent-warm);
        }

        /* Animated underline */
        .input-group::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 8px;
            height: 2px;
            width: 0;
            background: var(--accent-warm);
            border-radius: 4px;
            transition: width 0.35s ease;
        }

        .input-group input:focus+.input-underline {
            width: 100%;
        }

        /* Button */
        button {
            background: var(--accent-warm);
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--deep-teal);
            cursor: pointer;
            letter-spacing: 0.08em;
            box-shadow: 0 6px 12px rgba(198, 172, 143, 0.6);
            transition: background-color 0.3s ease, transform 0.2s ease;
            user-select: none;
        }

        button:hover {
            background: var(--earth-brown);
            color: var(--text-light);
            transform: scale(1.05);
        }

        button:active {
            transform: scale(0.98);
        }

        /* Small fade-in animation for container */
        @keyframes fadeInSlideUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            animation: fadeInSlideUp 0.6s ease forwards;
        }
    </style>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>


        let toast = <?php echo ((isset($_GET["msg"])) ? "true" : "false"); ?>;


        if(toast) {
            Toastify({
                text: "<?php echo htmlspecialchars($_GET["msg"]);?>",
                duration: 3000
            }).showToast();
        }
        
    </script>
</head>

<body>
    <form class="login-container" autocomplete="off" action="login.php" method="POST">
        <h2><?php 
        if(isset($_GET["msg"])) {
            echo htmlspecialchars($_GET["msg"]);
        }
        else {
            echo "Sign in!";
        }
        
        ?></h2>

        <div class="input-group">
            <input id="username" name="username" type="text" placeholder="Username" required />
            <span class="input-underline"></span>
        </div>

        <div class="input-group">
            <input id="password" name="password" type="password" placeholder="Password" required />
            <span class="input-underline"></span>
        </div>

        <button type="submit">LOGIN</button>
    </form>
</body>

</html>