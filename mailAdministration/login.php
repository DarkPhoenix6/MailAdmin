<?php
session_start();
require_once './includes/utils.php';
$displayBlock = '';
if ((filter_input(INPUT_POST, 'username')) && (filter_input(INPUT_POST, 'password'))) {
    require_once './includes/Auth.php';
    $username = filterEmail(filter_input(INPUT_POST, 'username'));
    $pass = test_input(filter_input(INPUT_POST, 'password'));
    $authcheck = new Auth();
    $_SESSION['auth'] = $authcheck->checkPassword($username, $pass);
    if ($_SESSION['auth']) {
        $_SESSION['username'] = $username;
   
        $_SESSION['last_activity'] = time(); //your last activity was now, having logged in.
        $_SESSION['expire_time'] = 0.25 * 60 * 60; // 15 minutes in seconds
        if ($_SESSION['prevPage']) {
            header("Location: " . $_SESSION['prevPage']);
            exit;
        } else {
            header("Location: AdminPage.php");
            exit;
        }
    } else {
        $displayBlock .= "<p class='error'>Username or Password is incorrect!</p>";
    }
}
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
    </head>
    <body>
        <main>
            <?php
            // put your code here
            echo $displayBlock;
            ?>
            <h1>Super Secret Page!</h1>
            <form id="login" name="login" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <fieldset> <legend> Login Form </legend>
                    <p><strong>username:</strong><br/>
                        <input type='text' id="username" name='username'/></p>
                    <p><strong>password:</strong><br/>
                        <input type='password' id="password" name='password'/></p>
                    <p><input type='submit' name='submit' value='login'/></p>
                </fieldset>
            </form>
        </main>
    </body>
</html>
