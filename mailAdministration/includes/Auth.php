<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Database.php';
require_once 'EmailQueries.php';

/**
 * Description of Auth
 *
 * @author cfedun
 */
class Auth {

    //put your code here
    /**
     * @var Database
     */
    protected $_database;
    /**
     * @var FALSE
     */
    protected $_connected;

    /**
     * Auth constructor.
     */
    function __construct() {
        $host = 'localhost';
        $port = '';
        $db = 'mailserver';
        $user = "mailadmin";
        $pass = 'letmein';
        $this->_database = new Database($host, $user, $pass, $db, $port);
        $this->_connected = $this->_database->connect();
    }

    /**
     * @param $pass
     * @param $salt
     * @return string
     */
    public function generateHashString($pass, $salt) {
        return "CONCAT('{SHA256-CRYPT}', ENCRYPT (" . $pass . ", CONCAT('$5$', " . $salt . ")))";
    }

    //Protected Methods
    /**
     * @param $passHash string
     * @return string
     */
    protected function getSalt($passHash) {
        $hashString = substr($passHash, 14);
        $salt = substr($hashString, 3, 16);
        return $salt;
    }

    /**
     * @param $UserName
     * @param $pass
     * @return bool
     */
    public function checkPassword($UserName, $pass) {
        $sql_query = "SELECT password FROM `AdminUsers` WHERE `username` = '" . $UserName . "'";
        $isError = $this->_database->getOneQueryRow($row, $sql_query);
        if (!$isError) {
            $hash = $row['password'];
            $salt = "'" . $this->getSalt($hash) . "'";

            $checkPassString = "SELECT " . $this->generateHashString("'" . $pass . "'", $salt) . " AS password";
            $this->_connected = $this->_database->connect();
            $isError = $this->_database->getOneQueryRow($row, $checkPassString);
            if (!$isError) {
                $verifyPass = $row['password'];
                if ($hash == $verifyPass) {
                    return TRUE;
                }
            } else {
                
            }
        }
        return FALSE;
    }

    /**
     * @param $pass
     */
    protected function createPass($pass) {
        $salt = "SUBSTRING(SHA(RAND()), -16)";
        $sqlPass = $this->generateHashString($pass, $salt);

    }

}
