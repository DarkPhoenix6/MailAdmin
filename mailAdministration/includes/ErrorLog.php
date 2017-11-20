<?php

require_once 'Database.php';
require_once 'utils.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorLog
 *
 * @author cfedun
 */
class ErrorLog {

    //put your code here
    protected $_logfile = '';
    // loglevel is the logging anything lessthan or equal to that number
    // 0: no logging
    // 1: errors
    // 2: warnings
    // 3: notice
    // 4: information
    // 5: debug
    protected $_loglevel = 3; 
    protected  $_useDBLogging = TRUE;
    protected $_database;
    // Constructor
    function __construct(bool $useDB, Database &$database, string $logPath) {
        if (is_null($logPath)) {
            $this->_logfile = '/var/tmp/MailAdmin.err.log';
        } else {
            $this->_logfile = $logPath . '/MailAdmin.err.log';
        }
        $this->_useDBLogging = $useDB;
        $this->_database = $database;
    }

    public function errorLog($logString) {
        file_put_contents($this->_logFile, print_r($logString, true), FILE_APPEND);
    }
    
    public function logEvent($severity, $eventType, $message ){
        
    }
    

    
}
