<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Author Chris Fedun
 */

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function strict_input_Filter($data) {
    return test_input($data);
}

function filterMultipleEmailPattern($data) {
    return ( 1 == preg_match('/^([\w+-.%]+@[\w\-.]+\.[A-Za-z]{2,4},*[\W]*)+$/', $data));
}

function filterEmailPattern($data) {
    return ( 1 == preg_match('/^([\w+-.%]+@[\w\-.]+\.[A-Za-z]{2,4},*[\W]*)$/', $data));
}

function filterEmail($email) {
    return mb_strtolower(strict_input_Filter($email));
}

function checkSpaces($data) {
    return ( 1 == preg_match('/\s/', $data));
}

function checkUserPattern($data) {
    return ( 1 == preg_match('/^([\w+-.%])/', $data));
}

function checkDomain($data) {
    return ( 1 == preg_match('/^([\w\-.]+\.[A-Za-z]{2,4})/', $data) );
}

function echoPagePath() {
    echo currentPagePath();
}

function currentPagePath() {
    return htmlspecialchars($_SERVER["PHP_SELF"]);
}

function currentPageName(){
    return basename(htmlspecialchars($_SERVER['PHP_SELF']));
}
function currentPageDirectory(){
    return dirname(htmlspecialchars($_SERVER['PHP_SELF']));
}

function isActiveCheck() {
    if ($_SESSION['last_activity'] < time() - $_SESSION['expire_time']) { //have we expired?
        // we don't want to destroy this session.... Yet...
        $_SESSION['destroy'] = FALSE;
        // Set to return to this page
        $_SESSION['prevPage'] = currentPagePath();
        //redirect to logout.php
        header('Location: logout.php');
    } else { //if we haven't expired:
        $_SESSION['destroy'] = TRUE; // Since we want to destroy the session if clicking logout
        $_SESSION['last_activity'] = time(); //this was the moment of last activity.
    }
}

function isAuthenticated() {
    if (!$_SESSION['auth']) {
        //redirect back to login form if not authorized
        $_SESSION['prevPage'] = currentPagePath();
        header("Location: login.php");
        exit;
    }
}

function getBrowser() {
    return getenv("HTTP_USER_AGENT");
}

function getClientIP() {
    return getenv('REMOTE_ADDR');
}

