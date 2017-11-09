<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Database.php';
require_once 'utils.php';

/**
 * Description of EmailData
 *
 * @author cfedun
 */
class EmailData {

    //put your code here
    protected $_downTriangleSmall = "&#x25BE;"; // 	&#9662;
    Protected $_upTriangleSmall = "&#9652;"; //	0x25B4

    const EMAIL_QUERY = "SELECT"
            . " domain_id AS idSourceDomain"
            . ",SUBSTR(email, 1, INSTR(email, '@')-1) AS SourceUser"
            . ",SUBSTR(email, INSTR(email, '@')+1) AS SourceDomain"
            . ",NULL AS DestinationUser"
            . ",NULL AS DestinationDomain"
            . ",NULL AS idDestinationUser"
            . " FROM `virtual_users`"
            . " UNION SELECT"
            . " alias.domain_id AS idDestinationDomain"
            . ",SUBSTR(alias.source, 1, INSTR(alias.source, '@')-1) AS SourceUser"
            . ",SUBSTR(alias.source, INSTR(alias.source, '@')+1) AS SourceDomain"
            . ",SUBSTR(alias.destination, 1, INSTR(alias.destination, '@')-1) AS DestinationUser"
            . ",SUBSTR(alias.destination, INSTR(alias.destination, '@')+1) AS DestinationDomain"
            . ",user.id AS idDestinationUser"
            . " FROM `virtual_aliases` AS alias"
            . " LEFT JOIN `virtual_users` AS user ON(user.email=alias.destination)";
    const SOURCEUSER = 'SourceUser ASC, SourceDomain ASC, DestinationUser ASC, DestinationDomain ASC';
    const SOURCEDOMAIN = 'SourceDomain ASC, SourceUser ASC, DestinationUser ASC, DestinationDomain ASC';
    const DESTINATIONUSER = 'DestinationUser ASC, DestinationDomain ASC, SourceUser ASC, SourceDomain ASC';
    const DESTINATIONDOMAIN = 'DestinationDomain ASC, DestinationUser ASC, SourceUser ASC, SourceDomain ASC';

    private $_DD = 'DestinationDomain';
    private $_DU = 'DestinationUser';
    private $_SU = 'SourceUser';
    private $_SD = 'SourceDomain';
    protected $_sortType;

    /**
     *
     * @var Database
     */
    protected $_database;
    protected $_emailQueryResult = '';
    protected $_connected = FALSE;

    // Constructor
    function __construct() {
        $host = 'localhost';
        $port = '';
        $db = 'mailserver';
        $user = "mailadmin";
        $pass = 'letmein';
        $this->_sortType = self::SOURCEUSER;
        $this->_database = new Database($host, $user, $pass, $db, $port);
        $this->_connected = $this->_database->connect();
        $succeed = $this->getEmailOverview();
    }

    function __destruct() {
        $this->_database->verifyTransactions();
    }

    public function connect() {
        $this->_connected = $this->_database->connect();
    }

    public function queryDB(&$result, $query) {
        return $this->_database->getQuery($result, $query);
    }

    public function generatePassString($pass) {
        return "CONCAT('{SHA256-CRYPT}', ENCRYPT ('"
                . $this->realEscape($pass)
                . "', CONCAT('$5$', SUBSTRING(SHA(RAND()), -16))))";
    }

    public function realEscape($escapeString) {
        return $this->_database->realEscapeString($escapeString);
    }

    public function updatePassword($email, $pass) {
        $password_query = "UPDATE virtual_users SET password="
                . $this->generatePassString($pass)
                . " WHERE email=" . $email . ";";
        $result = '';
        return $this->queryDB($result, $password_query);
    }

    public function createDomain($domain) {
        $sql = "INSERT INTO `virtual_domains` (`id`, `name`) VALUES (NULL, '"
                . $domain . "')";
        $this->_domainSubMethod($isError, $sql);
        return $isError;
    }

    public function deleteDomain($domain) {
        $sql = "DELETE FROM `virtual_domains` WHERE `name`='" . $domain . "'";
        $this->_domainSubMethod($isError, $sql);
        return $isError;
    }

    public function createUser($userName, $pass, $domain) {
        $email = $userName . '@' . $domain;
        $reEmail = $this->realEscape($email);
        $isError = $this->getDomainID($domain, $domain_id);
        if (!$isError) {
            $sql_VirtualUser = "INSERT INTO `virtual_users` (`id`, `domain_id`,"
                    . " `email`, `password`) VALUES (NULL, '" . $domain_id . "', '"
                    . $reEmail . "', " . $this->generatePassString($pass) . ")";
            $sql_VirtualAlias = "INSERT INTO `virtual_aliases` (`id`, `domain_id`,"
                    . " `source`, `destination`) VALUES (NULL, '" . $domain_id . "'"
                    . ", '" . $reEmail . "', '" . $reEmail . "')";
            $this->createUserSubMethod($isError, $sql_VirtualUser, $sql_VirtualAlias);
        }
        return $isError;
    }

    public function createUserSubMethod(&$isError, $sql_VirtualUser, $sql_VirtualAlias) {
        if (FALSE == ($this->_database->startTransaction())) {
            if (FALSE == ($isError = $this->_database->state($sql_VirtualUser))) {
                $isError = $this->modifyVirtualAliasSubmethod($isError, $sql_VirtualAlias);
            }
        } else {
            //Something Bad happened
            $isError = TRUE;
            $Error = $this->_database->cancelTransaction();
        }
//        var_dump($this->_database, $sql_VirtualAlias, $sql_VirtualUser);
        $Error = $this->_database->verifyTransactions();
    }

    public function modifyVirtualAliasSubmethod($isError, $sql_VirtualAlias) {
        if (FALSE == ($isError = $this->_database->state($sql_VirtualAlias))) {
            $Error = $this->_database->commitTransaction();
        }
        return $isError;
    }

    public function deleteUserSubMethod(&$isError, $sql_VirtualUser, $sql_VirtualAlias) {
        $this->createUserSubMethod($isError, $sql_VirtualUser, $sql_VirtualAlias);
    }

    public function getEmailOverview() {
        $sortType = $this->_sortType;
        $this->freeResult($this->_emailQueryResult);
        $query = self::EMAIL_QUERY . " ORDER BY " . $sortType;
        return $this->queryDB($this->_emailQueryResult, $query);
    }

    public function displayEmailOverview() {
        $isError = FALSE;
        $numRows = 0;
        $queryArray;
        $myTable = ''
                . '<thead><tr><th colspan="2">Email Address</th><th></th>'
                . '<th colspan="2">Alias Account</th></tr>'
                . '<tr><th onclick="submitSortForm(\'SOURCEUSER\', \'Overview\')">User</th><th onclick="submitSortForm(\'SOURCEDOMAIN\', \'Overview\')">Domain</th><th></th><th onclick="submitSortForm(\'DESTUSER\', \'Overview\')">User</th><th onclick="submitSortForm(\'DESTDOMAIN\', \'Overview\')">Domain</th></tr></thead><tbody>';
        if (FALSE !== ($isError = $this->getEmailOverview())) {
            ;
        } elseif (FALSE !== ($isError = $this->_database->getCount($this->_emailQueryResult, $numRows))) {
            ;
        } elseif ($numRows == 0) {
            $myTable .= '<tr class="" colspan="6">'
                    . '<td class="">No emails created yet. :)</td>'
                    . '</tr>';
        } else {
            $this->_displayEmail($myTable, $isError, $queryArray);
        }
        $myTable .= '</tbody>';

//        } elseif () {
//            ;
//        }

        if (!$isError) {
            //echo("<table>".$myTable."</table>");
            //var_dump($this->_emailQueryResult, $queryArray);
        } else {
            printf("error occured!");
        }
        return $myTable;
    }

    public function displayAccounts() {
        $isError = FALSE;
        $numRows = 0;
        $queryArray;
        $myTable = ''
                . '<thead><tr><th colspan="3">Email Accounts</th>'
                . '</tr>'
                . '<tr><th></th>'
                . '<th onclick="submitSortForm(\'SOURCEUSER\', \'Accounts\')">'
                . 'User</th><th onclick="submitSortForm(\'SOURCEDOMAIN\', '
                . '\'Accounts\')">Domain</th></tr></thead><tbody>';
        if (FALSE !== ($isError = $this->getEmailOverview())) {
            ;
        } elseif (FALSE !== ($isError = $this->_database->getCount($this->_emailQueryResult, $numRows))) {
            ;
        } elseif ($numRows == 0) {
            $myTable .= '<tr class="" colspan="3">'
                    . '<td class="">No emails created yet. :)</td>'
                    . '</tr>';
        } else {
            $this->_displayAccounts($myTable, $isError, $queryArray);
        }
        $myTable .= '</tbody>';

//        } elseif () {
//            ;
//        }

        if (!$isError) {
            //echo("<table>".$myTable."</table>");
            //var_dump($this->_emailQueryResult, $queryArray);
        } else {
            printf("error occured!");
        }
        return $myTable;
    }

    public function displayDomains() {
        $isError = FALSE;
        $numRows = 0;
        $queryArray;
        $myTable = ''
                . '<thead><tr><th></th><th >Domains</th>'
                . '</tr>'
                . '</thead><tbody>';
            $isError = FALSE;
        $myOptions = '';
        $result = FALSE;
        $queryArray;
        if (FALSE !== ($isError = $this->getDomains($result))) {
            ;
        } elseif (FALSE !== ($isError = $this->_database->getCount($result, $numRows))) {
            ;
        } elseif ($numRows == 0) {
            $myTable .= '<tr><td colspan="2">No Domains Created yet.</td></tr>';
        } else {
            $this->_displayDomains($myTable, $result, $isError, $queryArray);
        }
       $myTable .= '</tbody>';
        return $myTable;
    }

    public function getDomainID($domain, &$id) {
        $SqlQuery = "SELECT id FROM `virtual_domains` WHERE `name`='" . $domain . "'";
        $result;
        if (FALSE == ($isError = $this->_database->getOneQueryRow($result, $SqlQuery))) {
            $id = $result['id'];
        }
        return $isError;
    }

    Public function getDomains(&$result) {
        $isError = FALSE;
        $domainQuery = "SELECT name, id FROM `virtual_domains`";
        return $this->queryDB($result, $domainQuery);
    }

    public function getDomainOptions() {
        $isError = FALSE;
        $myOptions = '';
        $result = FALSE;
        $queryArray;
        if (FALSE !== ($isError = $this->getDomains($result))) {
            ;
        } elseif (FALSE !== ($isError = $this->_database->getCount($result, $numRows))) {
            ;
        } elseif ($numRows == 0) {
            $myOptions .= '<option>No Domains Created yet.</option>';
        } else {
            $this->_domainOptions($myOptions, $result, $isError, $queryArray);
        }
        return $myOptions;
    }

    public function freeResult(&$result) {
        $this->_database->freeResult($result);
    }

    public function addAlias($sourceUser, $sourceDomain, $destUser, $destDomain) {

        if (FALSE == ($this->_database->startTransaction())) {
            
        } else {
            //Something Bad happened
            $isError = TRUE;
            $Error = $this->_database->cancelTransaction();
        }
        $Error = $this->_database->verifyTransactions();
        return $isError;
    }

    public function setSort($sortString) {
        if ($sortString == 'SOURCEDOMAIN') {
            $this->_sortType = self::SOURCEDOMAIN;
        } elseif ($sortString == 'DESTDOMAIN') {
            $this->_sortType = self::DESTINATIONDOMAIN;
        } elseif ($sortString == 'DESTUSER') {
            $this->_sortType = self::DESTINATIONUSER;
        } else {
            $this->_sortType = self::SOURCEUSER;
        }
    }

    public function deleteUser($email) {
        $isError = FALSE;
        $deleteAlias = "DELETE FROM `virtual_aliases` WHERE `destination` = '"
                . $email . "'";
        $deleteUser = "DELETE FROM `virtual_users` WHERE `email` = '"
                . $email . "'";
        $this->deleteUserSubMethod($isError, $deleteUser, $deleteAlias);
        return $isError;
    }

    // PROTECTED METHODS
    protected function _domainSubMethod(&$isError, $query) {
        if (FALSE == ($this->_database->startTransaction())) {
            if (FALSE == ($isError = $this->_database->state($query))) {
                $Error = $this->_database->commitTransaction();
            }
        } else {
            //Something Bad happened
            $isError = TRUE;
            $Error = $this->_database->cancelTransaction();
        }
//        var_dump($this->_database, $sql_VirtualAlias, $sql_VirtualUser);
        $Error = $this->_database->verifyTransactions();
    }

    // PRIVATE METHODS

    private function _displayAccounts(&$myTable, &$isError, &$queryArray) {
        $SD_class = 'td_alignLeft';
        $UserAcctClass = 'td_alignRight';
        $accountColspan = 3;
        while (FALSE === ($isError = $this->_database->fetchArray($this->_emailQueryResult, $queryArray)) && $queryArray != NULL) {
//                if (!$isError) {
//                    var_dump($this->_emailQueryResult, $queryArray);
//                }
            $DU = $queryArray[$this->_DU];
            $DD = $queryArray[$this->_DD];
            $SU = $queryArray[$this->_SU];
            $SD = $queryArray[$this->_SD];
            $isAccount = (NULL == $DU);

            if ($isAccount) {
                $myTable .= '<tr>'
                        . '<td class=""><span onclick="'
                        . 'submitDelete(\'' . $SU . '\', \'' . $SD . '\', \'Accounts\')">'
                        . ' &#xd7;&nbsp;Delete</span></td>'
                        . '<td class="' . $UserAcctClass . '">' . $SU . '</td>'
                        . '<td class="' . $SD_class . '">@' . $SD . '</td>'
                        . '</tr>';
            } else {
                ;
            }
        }
    }

    private function _displayEmail(&$myTable, &$isError, &$queryArray) {
        $SD_class = 'td_alignLeft';
        $UserAcctClass = 'td_alignRight';
        $accountColspan = 3;
        while (FALSE === ($isError = $this->_database->fetchArray($this->_emailQueryResult, $queryArray)) && $queryArray != NULL) {
//                if (!$isError) {
//                    var_dump($this->_emailQueryResult, $queryArray);
//                }
            $DU = $queryArray[$this->_DU];
            $DD = $queryArray[$this->_DD];
            $SU = $queryArray[$this->_SU];
            $SD = $queryArray[$this->_SD];
            $isAccount = (NULL == $DU);

            $myTable .= '<tr>'
                    . '<td class="' . $UserAcctClass . '">' . $SU . '</td>'
                    . '<td class="' . $SD_class . '">@' . $SD . '</td>';
            if ($isAccount) {
                $myTable .= '<td colspan="' . $accountColspan . '">'
                        . 'Is an account.</td>';
            } else {
                $myTable .= '<td >Is an Alias of:</td>'
                        . '<td class="' . $UserAcctClass . '">' . $DU . '</td>'
                        . '<td class="' . $SD_class . '">@' . $DD . '</td>';
            }
            $myTable .= '</tr>';
        }
    }

    private function _domainOptions(&$myOptions, $result, &$isError, &$queryArray) {
        while (FALSE === ($isError = $this->_database->fetchArray($result, $queryArray)) && $queryArray != NULL) {
            $myOptions .= '<option value="' . $queryArray["name"] . '">' . $queryArray['name'] . '</option>';
        }
    }
    private function _displayDomains(&$myTable, $result, &$isError, &$queryArray) {
        while (FALSE === ($isError = $this->_database->fetchArray($result, $queryArray)) && $queryArray != NULL) {
            $myTable .= '<tr>'
                        . '<td class=""><span onclick="'
                        . 'submitDomainDelete(\''. $queryArray["name"] . '\', \'Domains\')">'
                        . ' &#xd7;&nbsp;Delete</span></td>'
                        . '<td class="">' . $queryArray["name"] . '</td>'
                        . '</tr>';
        }
    }
}
