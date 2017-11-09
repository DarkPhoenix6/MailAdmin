<?php
require_once "./includes/EmailData.php";
require_once './includes/utils.php';
session_start();
$displayBlock = '';
if (!$_SESSION['auth']) {
    //redirect back to login form if not authorized
    $_SESSION['prevPage'] = htmlspecialchars($_SERVER["PHP_SELF"]);
    header("Location: login.php");
    exit;
}
if ($_SESSION['last_activity'] < time() - $_SESSION['expire_time']) { //have we expired?
    //redirect to logout.php
    $_SESSION['prevPage'] = htmlspecialchars($_SERVER["PHP_SELF"]);
    header('Location: logout.php');
} else { //if we haven't expired:
    $_SESSION['last_activity'] = time(); //this was the moment of last activity.
}
if ($_SESSION['emailDB']) {
    $mysqli = $_SESSION['emailDB'];
    $mysqli->connect();
//    var_dump($_POST);
    if (filter_input(INPUT_POST, 'sortType')) {
        $sortType = test_input(filter_input(INPUT_POST, 'sortType'));
        $mysqli->setSort($sortType);
    }elseif (filter_input(INPUT_POST, 'user') && filter_input(INPUT_POST, 'domain') && filter_input(INPUT_POST, 'password') && filter_input(INPUT_POST, 'user') !== ' ' && filter_input(INPUT_POST, 'password') !== ' ') {
        $user = mb_strtolower(strict_input_Filter(filter_input(INPUT_POST, 'user')));
        $domain = strict_input_Filter(filter_input(INPUT_POST, 'domain'));
        $pass = strict_input_Filter(filter_input(INPUT_POST, 'password'));
//        var_dump($user, $domain, $pass);
        $isError = $mysqli->createUser($user, $pass, $domain);
//        var_dump($isError);
        if ($isError) {
            $displayBlock .= '<p class="error">Error Occured</p>';
        }else{
            $displayBlock .= '<p class="success">Successfully created account</p>';
        }
    }elseif (filter_input(INPUT_POST, 'user') === ' ' ) {
        $displayBlock .= '<p class="error">Invalid Username</p>';
    }elseif (filter_input(INPUT_POST, 'password') === ' ' ) {
        $displayBlock .= '<p class="error">Invalid Password</p>';
    }elseif (filter_input(INPUT_POST, 'deleteDomain') && filter_input(INPUT_POST, 'deleteUsername')){
        $user = strict_input_Filter(filter_input(INPUT_POST, 'deleteUsername')) 
                . '@' 
                . strict_input_Filter(filter_input(INPUT_POST, 'deleteDomain'));
        $isError = $mysqli->deleteUser($user);
        if ($isError) {
            $displayBlock .= '<p class="error">Error Occured</p>';
        }else{
            $displayBlock .= '<p class="success">Successfully deleted account</p>';
        }
    }
//    var_dump($mysqli, $_POST);
} else {
    $mysqli = new EmailData();
    $_SESSION['emailDB'] = $mysqli;
//    var_dump($mysqli);
}
$myTable = $mysqli->displayAccounts();
$myDomains = $mysqli->getDomainOptions();
$_SESSION['emailDB'] = $mysqli;
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
        <title>Account Creation</title>
        <link rel="stylesheet" href="css/accounts.css" />
        <link rel="stylesheet" href="css/mobile_menu.css" />
        <link rel="stylesheet" href="css/scrollingTable.css" />
    </head>
    <body>
        <div class="parent clear">
            <nav class="child" id="mobile_menu"></nav>
            <nav class="child" id="navbar">
                <ul>
                    <li>
                        <a href="AdminPage.php" >Home</a>
                    </li>

                    <li>
                        <a href="#account" class="current" >Accounts</a>
                    </li>
                    <li>
                        <a href="#domains" >Domains</a>
                    </li>
                    <li>
                        <a href="#aliases" >Aliases</a>
                    </li>
                    <li>
                        <a href="#adminAccounts">Admin Accounts</a>
                    </li>
                    <li>
                        <a href="logout.php" >Logout</a>
                    </li>
                </ul>
            </nav>
        </div>
        <main class="main">
            <?php echo $displayBlock; ?>
            <form id="Accounts" method="post" 
                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input id="sortType" type="hidden" name="sortType" value="" />
                <input id="deleteUsername" type="hidden" name="deleteUsername" value="" />
                <input id="deleteDomain" type="hidden" name="deleteDomain" value="" />
                <div>
                    email addresses
                </div>
                <table class="scroll">
                    
                    <?php
                    // put your code here
                    echo $myTable;
                    ?>
                </table></form>

            <form id="accountcreation" method="post" 
                  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <fieldset>
                    <legend> Account Creation </legend>
                    <label for="user">Email:</label>
                   
                    <input type="hidden" name="createAccount" id="createAccount" value="1"/>
                    <input name ="user" type="text" required="" autocomplete="off" /> @ 
                    <select name="domain" required="">
                        <?php
                        echo $myDomains;
                        ?>
                    </select>
                    <br>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required="" autocomplete="off" />
                </fieldset>
                <input type="submit" name="submit" value="Submit" />
            </form>
        </main>
        <script src="js/require.js"></script>
        <script src="js/navbar.js"></script>
        <script src="js/utils.js"></script>
        <script src="js/tableScrollbarAccount.js"></script>
    </body>
</html>
