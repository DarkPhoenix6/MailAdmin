<?php
/**
 * Created by PhpStorm.
 * User: cfedun
 * Date: 22/11/17
 * Time: 6:47 PM
 */
require_once "./includes/EmailData.php";
require_once './includes/utils.php';
session_start();


list($displayBlock, $mysqli) = pageStart();

$_SESSION['emailDB'] = $mysqli;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo currentPageName();?></title>
    <meta name="description" content="">
    <meta name="author" content="Chris Fedun">
    <meta name="viewport" content="width=device-width; initial-scale=1.0">
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
                <a href="AdminPage.php" >Home</a>
            </li>
            <li>
                <a href="Account.php" >Accounts</a>
            </li>
            <li>
                <a href="Domains.php" >Domains</a>
            </li>
            <li>
                <a href="Aliases.php"  >Aliases</a>
            </li>
            <li>
                <a href="#adminAccounts" class="current">Admin Accounts</a>
            </li>
            <li>
                <a href="logout.php" >Logout</a>
            </li>
        </ul>
    </nav>
</div>
<main>
    <div class="main">
        <?php echo "$displayBlock"; ?>
        <form id="Admin" class="current" name="Admin" method="post"
              action="<?php echoPagePath(); ?>">
            <input id="sortType"type="hidden" name="sortType" value="" />

            <table class="scroll">
                <?php echo "$myTable"; ?>
            </table>
        </form>
        <form method="post" action="<?php echoPagePath(); ?>">
            <fieldset>
            <input type="hidden" name="createAdmin" value="1">
                <input type="submit" value="Submit">
            </fieldset>
        </form>
    </div>

</body>
</html>