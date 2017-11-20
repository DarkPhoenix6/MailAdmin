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
    $_SESSION['destroy'] = FALSE;
    $_SESSION['prevPage'] = htmlspecialchars($_SERVER["PHP_SELF"]);
    header('Location: logout.php');
} else { //if we haven't expired:
    $_SESSION['destroy'] = TRUE; // Since we want to destroy the session if clicking logout
    $_SESSION['last_activity'] = time(); //this was the moment of last activity.
}
if ($_SESSION['emailDB']) {
    $mysqli = $_SESSION['emailDB'];
    $mysqli->connect();
//    var_dump($_POST);
    if (filter_input(INPUT_POST, 'domain') && filter_input(INPUT_POST, 'domain') !== ' ' && checkDomain(filter_input(INPUT_POST, 'domain'))) {

        $domain = strict_input_Filter(filter_input(INPUT_POST, 'domain'));

//        var_dump($user, $domain, $pass);
        $isError = $mysqli->createDomain($domain);
//        var_dump($isError);
        if ($isError) {
            $displayBlock .= '<p class="error">Error Occured</p>';
        } else {
            $displayBlock .= '<p class="success">Successfully created domain</p>';
        }
    } elseif (filter_input(INPUT_POST, 'domain') === ' ' || (filter_input(INPUT_POST, 'domain') && !checkDomain(filter_input(INPUT_POST, 'domain')))) {
        $displayBlock .= '<p class="error">Invalid domain</p>';
    } elseif (filter_input(INPUT_POST, 'deleteDomain')) {
        $domain = strict_input_Filter(filter_input(INPUT_POST, 'deleteDomain'));
        $isError = $mysqli->deleteDomain($domain);
        if ($isError) {
            $displayBlock .= '<p class="error">Error Occured</p>';
        } else {
            $displayBlock .= '<p class="success">Successfully deleted domain</p>';
        }
    }
//    var_dump($mysqli, $_POST);
} else {
    $mysqli = new EmailData();
    $_SESSION['emailDB'] = $mysqli;
//    var_dump($mysqli);
}
$myTable = $mysqli->displayDomains();
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
                        <a href="Account.php"  >Accounts</a>
                    </li>
                    <li>
                        <a href="#domains" class="current">Domains</a>
                    </li>
                    <li>
                        <a href="Aliases.php" >Aliases</a>
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
            <div class="fieldset">
                <h1><span>Domains</span></h1>
                <div class="clear">
                    <form id="Domains" method="post" 
                          action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input id="sortType" type="hidden" name="sortType" value="" />
                        <input id="deleteDomain" type="hidden" name="deleteDomain" value="" />

                        <table class="scroll" id="myTable">

                            <?php
                            // put your code here
                            echo $myTable;
                            ?>
                        </table></form>
                    <div class="flex">
                        <form class="column" name="domainCreation" id="domainCreation" method="post" 
                              action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <fieldset>
                                <legend> Domain Creation </legend>
                                <label for="domain">Domain:</label>
                                <input id="domain" name ="domain" type="text" required="" autocomplete="off" />  
                                <input type="hidden" name="createDomain" id="createAccount" value="1"/>
                            </fieldset>
                            <input type="submit" name="submit" value="Submit" />
                        </form>
                    </div>
                </div>
            </div>
            <footer>
                <p>&copy; Copyright  by Chris Fedun</p>
            </footer>
        </main>
        <script src="js/require.js"></script>
        <script src="js/navbar.js"></script>
        <script src="js/utils.js"></script>
        <script src="js/sortTable.js"></script>
        <script src="js/tableScrollbar.js"></script>
    </body>
</html>
