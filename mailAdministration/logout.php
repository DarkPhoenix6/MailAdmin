<?php
session_start();
$_SESSION['auth']= FALSE;
header("Location: AdminPage.php");
exit;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

