<?php
require_once "./includes/EmailData.php";
require_once './includes/utils.php';
session_start();
if (!$_SESSION['auth']) {
    //redirect back to login form if not authorized
    $_SESSION['prevPage'] = htmlspecialchars($_SERVER["PHP_SELF"]);
    header("Location: login.php");
    exit;
}
if ($_SESSION['last_activity'] < time() - $_SESSION['expire_time']) { //have we expired?
    // we don't want to destroy this session.... Yet...
    $_SESSION['destroy'] = FALSE;
    // Set to return to this page
    $_SESSION['prevPage'] = htmlspecialchars($_SERVER["PHP_SELF"]);
    //redirect to logout.php
    header('Location: logout.php');
} else { //if we haven't expired:
    $_SESSION['destroy'] = TRUE; // Since we want to destroy the session if clicking logout
    $_SESSION['last_activity'] = time(); //this was the moment of last activity.
}
if ($_SESSION['emailDB']) {
    $mysqli = $_SESSION['emailDB'];
    $mysqli->connect();
    if (filter_input(INPUT_POST, 'sortType')) {
        $sortType = test_input(filter_input(INPUT_POST, 'sortType'));
        $mysqli->setSort($sortType);
    }
//    var_dump($mysqli, $_POST);
} else {
    $mysqli = new EmailData();
    $_SESSION['emailDB'] = $mysqli;
//    var_dump($mysqli);
}
$myTable = $mysqli->displayEmailOverview();
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
        <title>Admin Page</title>
        <link rel="stylesheet" href="css/index.css" />
        <link rel="stylesheet" href="css/scrollingTable.css" />
    </head>
    <body>
        <div class="parent clear">
            <nav class="child" id="mobile_menu"></nav>
            <nav class="child" id="navbar">
                <ul>
                    <li>
                        <a href="#Overview" class="current" onclick="setNavClasses(0);">Home</a>
                    </li>

                    <li>
                        <a href="Account.php" >Accounts</a>
                    </li>
                    <li>
                        <a href="Domains.php" >Domains</a>
                    </li>
                    <li>
                        <a href="Aliases.php" >Aliases</a>
                    </li>
                    <li>
                        <a href="#adminAccounts" onclick="setNavClasses(4);">Admin Accounts</a>
                    </li>
                    <li>
                        <a href="logout.php" >Logout</a>
                    </li>
                </ul>
            </nav>
        </div>
        <main>
            <div class="main">
                <form id="Overview" class="current" name="Overview" method="post" 
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input id="sortType"type="hidden" name="sortType" value="SOURCEUSER" />

                    <table class="scroll">
                        <?php echo "$myTable"; ?>
                    </table>
                </form>
                <form id="account">
                    hi
                    This is the Account page
                    <a href="Account.php" >Click here to create an email account</a>
                </form>
                <?php // echo var_dump($_SESSION); ?>
                <footer>
                    <p>&copy; Copyright  by Chris Fedun</p>
                </footer>
            </div>
            <script src="js/require.js"></script>
            <script src="js/navbar.js"></script>
            <script src="js/utils.js"></script>
            <script src="js/tableScrollbarOverview.js"></script>
        </main>
    </body>
</html>
