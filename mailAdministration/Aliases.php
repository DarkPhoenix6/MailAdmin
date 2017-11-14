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
$myTable = $mysqli->displayAliases();
$myDomains = $mysqli->getDomainOptions();
$myAccounts = $mysqli->getAccountOptions();
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
        <title>Aliases and Redirects</title>
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
                        <a href="Account.php" >Accounts</a>
                    </li>
                    <li>
                        <a href="Domains.php" >Domains</a>
                    </li>
                    <li>
                        <a href="#aliases" class="current" >Aliases</a>
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
        <main>
            <div class="main">
                <form id="Aliases" class="current" name="Aliases" method="post" 
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input id="sortType"type="hidden" name="sortType" value="" />

                    <table class="scroll">
                        <?php echo "$myTable"; ?>
                    </table>
                </form>
                <div class="flex">
                    <form class="column" id="aliascreation" name="aliascreation" method="post" 
                          action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <fieldset>
                            <legend> Alias Creation </legend>
                            <label for="user">Alias:</label>


                            <input name ="user" type="text" required="" autocomplete="off" /> @ 
                            <select name="domain" required="">
                                <?php
                                echo $myDomains;
                                ?>
                            </select>
                            <br>
                            <label for="destination">Destination Account:</label>


<!--<input name ="userDesr" type="text" required="" autocomplete="off" /> @--> 
                            <select id="destination" name="destination" required="">
                                <?php
                                echo $myAccounts;
                                ?>
                            </select>
                            <br>
                            <input type="hidden" name="createAlias" id="createAlias" value="1"/>
                        </fieldset>
                        <input type="submit" name="submitAlias" value="Submit" />
                    </form>
                </div>
                    <br>
                    <div class="flex">
                    <form class="column" id="redirectCreation" name="redirectCreation" method="post" 
                          action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <fieldset>
                            <legend> Redirect/Forwarding Creation </legend>
                            <label for="userRedirect">Alias:</label>


                            <input name ="userRedirect" type="text" required="" autocomplete="off" /> @ 
                            <select name="domain" required="">
                                <?php
                                echo $myDomains;
                                ?>
                            </select>
                            <br>
                            <label for="destinationRedir">Destination:</label>


<!--<input name ="userDesr" type="text" required="" autocomplete="off" /> @--> 
                            <input type="email" id="destinationRedir" name="destination"  required="" autocomplete="off" />

                            <input type="hidden" name="createRedirect" id="createRedirect" value="1"/>
                            <br>
                        </fieldset>
                        <input type="submit" name="submitRedirect" value="Submit" />
                    </form>
                </div>
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
