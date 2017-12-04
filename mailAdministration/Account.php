<?php
require_once "./includes/EmailData.php";
require_once './includes/utils.php';
session_start();
$displayBlock = '';
isAuthenticated();
isActiveCheck();
if ($_SESSION['emailDB']) {
    $mysqli = $_SESSION['emailDB'];
    $mysqli->connect();

//    var_dump($_POST);
    if (filter_input(INPUT_POST, 'sortType')) {
        $sortType = test_input(filter_input(INPUT_POST, 'sortType'));
        $mysqli->setSort($sortType);
    } elseif (filter_input(INPUT_POST, 'deleteDomain') && filter_input(INPUT_POST, 'deleteUsername')) {
        $user = strict_input_Filter(filter_input(INPUT_POST, 'deleteUsername'))
                . '@'
                . strict_input_Filter(filter_input(INPUT_POST, 'deleteDomain'));
        $isError = $mysqli->deleteUser($user);
        if ($isError) {
            $displayBlock .= '<p class="error">Error Occured</p>';
        } else {
            $displayBlock .= '<p class="success">Successfully deleted account</p>';
        }
    } elseif (filter_input(INPUT_POST, "createAccount") == 1) {
        if (filter_input(INPUT_POST, 'user') && filter_input(INPUT_POST, 'domain') && filter_input(INPUT_POST, 'password') && filter_input(INPUT_POST, 'user') !== ' ' && filter_input(INPUT_POST, 'password') !== ' ') {
            $user = mb_strtolower(strict_input_Filter(filter_input(INPUT_POST, 'user')));
            $domain = strict_input_Filter(filter_input(INPUT_POST, 'domain'));
            $pass = strict_input_Filter(filter_input(INPUT_POST, 'password'));
//        var_dump($user, $domain, $pass);
            $isError = $mysqli->createUser($user, $pass, $domain);
//        var_dump($isError);
            if ($isError) {
                $displayBlock .= '<p class="error">Error Occured</p>';
            } else {
                $displayBlock .= '<p class="success">Successfully created account</p>';
            }
        } elseif (filter_input(INPUT_POST, 'user') === ' ' || !checkUserPattern(filter_input(INPUT_POST, 'user'))) {
            $displayBlock .= '<p class="error">Invalid Username</p>';
        } elseif (filter_input(INPUT_POST, 'password') === ' ') {
            $displayBlock .= '<p class="error">Invalid Password</p>';
        }
    } elseif (filter_input(INPUT_POST, "passReset") == 1) {
        $user = mb_strtolower(strict_input_Filter(filter_input(INPUT_POST, 'userR')));
        $pass = strict_input_Filter(filter_input(INPUT_POST, 'passwordReset'));
        if ($user && $pass && $pass !== " " && filterEmailPattern($user)) {
            $isError = $mysqli->updatePassword($user, $pass);
//       var_dump($isError, $mysqli);
            if ($isError) {

                $displayBlock .= '<p class="error">Error Occured</p>';
            } else {
                $displayBlock .= '<p class="success">Successfully updated Password</p>';
            }
        } elseif (!filterEmailPattern($user) || !filter_input(INPUT_POST, 'userR')) {
            $displayBlock .= '<p class="error">Invalid Username</p>';
        } elseif (filter_input(INPUT_POST, 'passwordReset') === ' ') {
            $displayBlock .= '<p class="error">Invalid Password</p>';
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
                        <a href="Domains.php" >Domains</a>
                    </li>
                    <li>
                        <a href="Aliases.php" >Aliases</a>
                    </li>
                    <li>
                        <a href="Admin.php">Admin Accounts</a>
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
                <h1><span>Email Accounts</span></h1>
                <div class="clear">
                    <form class="clear" id="Accounts" method="post" 
                          action="<?php echoPagePath(); ?>">
                        <input id="sortType" type="hidden" name="sortType" value="" />
                        <input id="deleteUsername" type="hidden" name="deleteUsername" value="" />
                        <input id="deleteDomain" type="hidden" name="deleteDomain" value="" />
                        <table class="scroll">

                            <?php
                            echo $myTable;
                            ?>
                        </table></form>
                    <div class="flex">
                        <form class="column" id="accountcreation" method="post" 
                              action="<?php echoPagePath(); ?>">
                            <fieldset>
                                <legend> Account Creation </legend>
                                <label for="user">Email:</label>
                                <input name ="user" type="text" required="" autocomplete="off" /> @ 
                                <select name="domain" required="">
                                    <?php
                                    echo $myDomains;
                                    ?>
                                </select>
                                <br>
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required="" autocomplete="off" />
                                <input type="hidden" name="createAccount" id="createAccount" value="1"/>
                            </fieldset>
                            <input type="submit" name="submit" value="Submit" />
                        </form>
                    </div>
                    <div class="flex">
                        <form class="column" id="resetPass" method="post" 
                              action="<?php echoPagePath(); ?>">
                            <fieldset>
                                <legend> Password Reset </legend>
                                <label for="userR">User:</label>
                                <select id="userR" name="userR" required="">
                                    <?php
                                    echo $myAccounts;
                                    ?>
                                </select>
                                <br>
                                <label for="passwordReset">Password:</label>
                                <input type="password" id="passwordReset" name="passwordReset" required="" autocomplete="off" />
                                <input type="hidden" name="passReset" id="passReset" value="1"/>
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
        <script src="js/require.min.js"></script>
        <script src="js/navbar.min.js"></script>
        <script src="js/utils.min.js"></script>
        <script src="js/tableScrollbarAccount.js"></script>
    </body>
</html>
