<?php
require_once "./includes/EmailData.php";
require_once './includes/utils.php';
session_start();
isAuthenticated();
isActiveCheck();
$displayBlock = '';
if ($_SESSION['emailDB']) {
    $mysqli = $_SESSION['emailDB'];
    $mysqli->connect();
    if (filter_input(INPUT_POST, 'sortType')) {
        $sortType = test_input(filter_input(INPUT_POST, 'sortType'));
        $mysqli->setSort($sortType);
    } elseif (filter_input(INPUT_POST, 'createAlias')) {
        if (checkUserPattern(filter_input(INPUT_POST, 'user'))) {
            $user = strict_input_Filter(filter_input(INPUT_POST, 'user'));
            if (checkDomain(filter_input(INPUT_POST, 'domain'))) {
                $domain = strict_input_Filter(filter_input(INPUT_POST, 'domain'));
                if (filterEmailPattern(filter_input(INPUT_POST, 'destination'))) {
                    $destination = strict_input_Filter(filter_input(INPUT_POST, 'destination'));
                    if (FALSE == ($isError = $mysqli->createAlias($user, $domain, $destination))) {
                        $displayBlock .= "<p class='success' >Successfully created Alias!</p>";
                    } else {
                        $displayBlock .= "<p class='error' >Error Occured</p>";
                    }
                } else {
                    $displayBlock .= "<p class='error' >Invalid destination</p>";
                }
            } else {
                $displayBlock .= "<p class='error' >Invalid domain</p>";
            }

        } else {
            $displayBlock .= "<p class='error' >Invalid username</p>";
        }
    } elseif (filter_input(INPUT_POST, 'createRedirect')) {
        if (checkUserPattern(filter_input(INPUT_POST, 'userRedirect'))) {
            $user = strict_input_Filter(filter_input(INPUT_POST, 'userRedirect'));
            if (checkDomain(filter_input(INPUT_POST, 'domainRedirect'))) {
                $domain = strict_input_Filter(filter_input(INPUT_POST, 'domainRedirect'));
                if (filterEmailPattern(filter_input(INPUT_POST, 'destinationRedir'))) {
                    $destination = strict_input_Filter(filter_input(INPUT_POST, 'destinationRedir'));
                    if (FALSE == ($isError = $mysqli->createAlias($user, $domain, $destination))) {
                        $displayBlock .= "<p class='success' >Successfully created Alias!</p>";
                    } else {
                        $displayBlock .= "<p class='error' >Error Occured</p>";
                    }
                } else {
                    $displayBlock .= "<p class='error' >Invalid destination</p>";
                }
            } else {
                $displayBlock .= "<p class='error' >Invalid domain</p>";
            }

        } else {
            $displayBlock .= "<p class='error' >Invalid username</p>";
        }
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
    <link rel="stylesheet" href="css/accounts.css"/>
    <link rel="stylesheet" href="css/mobile_menu.css"/>
    <link rel="stylesheet" href="css/scrollingTable.css"/>
</head>
<body>
<div class="parent clear">
    <nav class="child" id="mobile_menu"></nav>
    <nav class="child" id="navbar">
        <ul>
            <li>
                <a href="AdminPage.php">Home</a>
            </li>

            <li>
                <a href="Account.php">Accounts</a>
            </li>
            <li>
                <a href="Domains.php">Domains</a>
            </li>
            <li>
                <a href="#aliases" class="current">Aliases</a>
            </li>
            <li>
                <a href="Admin.php">Admin Accounts</a>
            </li>
            <li>
                <a href="logout.php">Logout</a>
            </li>
        </ul>
    </nav>
</div>
<main>
    <div class="main">
        <?php echo "$displayBlock"; ?>
        <div class="fieldset">
            <h1><span>Aliases</span></h1>
            <div class="clear">
                <form id="Aliases" class="current" name="Aliases" method="post"
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input id="sortType" type="hidden" name="sortType" value=""/>

                    <table class="scroll">
                        <?php echo "$myTable"; ?>
                    </table>
                </form>
                <div class="flex">
                    <form class="column" id="aliascreation" name="aliascreation" method="post"
                          action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <fieldset>
                            <legend> Alias Creation</legend>
                            <label for="user">Alias:</label>


                            <input id="user" name="user" type="text" required="" autocomplete="off"/> @
                            <select name="domain" required="">
                                <?php
                                echo $myDomains;
                                ?>
                            </select>
                            <br>
                            <label for="destination">Destination:</label>


                            <!--<input name ="userDesr" type="text" required="" autocomplete="off" /> @-->
                            <select id="destination" name="destination" required="">
                                <?php
                                echo $myAccounts;
                                ?>
                            </select>
                            <br>
                            <input type="hidden" name="createAlias" id="createAlias" value="1"/>
                        </fieldset>
                        <input type="submit" name="submitAlias" value="Submit"/>
                    </form>
                </div>
                <br>
                <div class="flex">
                    <form class="column" id="redirectCreation" name="redirectCreation" method="post"
                          action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <fieldset>
                            <legend> Redirect/Forwarding Creation</legend>
                            <label for="userRedirect">Alias:</label>
                            <input id="userRedirect" name="userRedirect" type="text" required="" autocomplete="off"/> @
                            <select name="domainRedirect" required="">
                                <?php
                                echo $myDomains;
                                ?>
                            </select>
                            <br>
                            <label for="destinationRedir">Destination:</label>


                            <!--<input name ="userDesr" type="text" required="" autocomplete="off" /> @-->
                            <input type="email" id="destinationRedir" name="destination" required=""
                                   autocomplete="off"/>

                            <input type="hidden" name="createRedirect" id="createRedirect" value="1"/>
                            <br>
                        </fieldset>
                        <input type="submit" name="submitRedirect" value="Submit"/>
                    </form>
                </div>
            </div>
        </div>
        <?php // echo var_dump($_SESSION); ?>
        <footer>
            <p>&copy; Copyright by Chris Fedun</p>
        </footer>
    </div>
    <script src="js/require.min.js"></script>
    <script src="js/navbar.min.js"></script>
    <script src="js/utils.min.js"></script>
    <script src="js/tableScrollbarOverview.js"></script>
</main>
</body>
</html>
