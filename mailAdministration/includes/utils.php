<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
