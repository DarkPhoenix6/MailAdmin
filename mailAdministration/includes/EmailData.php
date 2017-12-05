<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Database.php';
require_once 'utils.php';
require_once 'EmailQueries.php';
//require_once 'Auth.php';

/**
 * Description of EmailData
 *
 * @author cfedun
 */
class EmailData {

    //put your code here
    //protected $_downTriangleSmall = "&#x25BE;"; // 	&#9662;
    //Protected $_upTriangleSmall = "&#9652;"; //	0x25B4


    /**
     *
     */
    const SOURCEUSER = 'SourceUser ASC, SourceDomain ASC, DestinationUser ASC, DestinationDomain ASC';
    /**
     *
     */
    const SOURCEDOMAIN = 'SourceDomain ASC, SourceUser ASC, DestinationUser ASC, DestinationDomain ASC';
    /**
     *
     */
    const DESTINATIONUSER = 'DestinationUser ASC, DestinationDomain ASC, SourceUser ASC, SourceDomain ASC';
    /**
     *
     */
    const DESTINATIONDOMAIN = 'DestinationDomain ASC, DestinationUser ASC, SourceUser ASC, SourceDomain ASC';

    //protected $_emailQueries = EmailQueries;
    /**
     * @var string
     */
    private $_DD = 'DestinationDomain';
    /**
     * @var string
     */
    private $_DU = 'DestinationUser';
    /**
     * @var string
     */
    private $_SU = 'SourceUser';
    /**
     * @var string
     */
    private $_SD = 'SourceDomain';
    /**
     * @var string
     */
    protected $_sortType;
    /**
     * @var
     */
    public $log;
    /**
     *
     * @var Database
     */
    protected $_database;
    /**
     * @var string
     */
    protected $_emailQueryResult = '';
    /**
     * @var bool|FALSE
     */
    protected $_connected = FALSE;
    /**
     * @var
     */
    protected $_logpath;
    // Constructor

    /**
     * EmailData constructor.
     */
    function __construct() {
        $host = 'localhost';
        $port = '';
        $db = 'mailserver';
        $user = "mailadmin";
        $pass = 'letmein';
        $this->_sortType = self::SOURCEUSER;
        $this->_database = new Database($host, $user, $pass, $db, $port);
        $this->_connected = $this->_database->connect();
        //$this->log = new ErrorLog( TRUE, $this->_database, $logPath);
        $succeed = $this->getEmailOverview();
    }

    /**
     *
     */
    function __destruct() {
        $this->_database->verifyTransactions();
    }

    /**
     *
     */
    public function connect() {
        $this->_connected = $this->_database->connect();
    }

    /**
     * @param $result
     * @param $query
     * @return bool
     */
    public function queryDB(&$result, $query) {
        return $this->_database->getQuery($result, $query);
    }

    /**
     * @param $pass
     * @return string
     */
    public function generatePassString($pass) {
        return "CONCAT('{SHA256-CRYPT}', ENCRYPT ('"
                . $this->realEscape($pass)
                . "', CONCAT('$5$', SUBSTRING(SHA(RAND()), -16))))";
    }

    /**
     * @param $escapeString
     * @return string
     */
    public function realEscape($escapeString) {
        return $this->_database->realEscapeString($escapeString);
    }

    /**
     * @param $email
     * @param $pass
     * @return bool
     */
    public function updatePassword($email, $pass) {
        $password_query = "UPDATE virtual_users SET password="
                . $this->generatePassString($pass)
                . " WHERE `email`='" . $email . "';";
        $isError = FALSE;
        $this->_passwordReset($isError, $password_query);
        return $isError;
    }

    /**
     * @param $domain
     * @return mixed
     */
    public function createDomain($domain) {
        $sql = "INSERT INTO `virtual_domains` (`id`, `name`) VALUES (NULL, '"
                . $domain . "')";
        $this->_domainSubMethod($isError, $sql);
        return $isError;
    }

    /**
     * @param $domain
     * @return mixed
     */
    public function deleteDomain($domain) {
        $sql = "DELETE FROM `virtual_domains` WHERE `name`='" . $domain . "'";
        $this->_domainSubMethod($isError, $sql);
        return $isError;
    }

    /**
     * @param $userName
     * @param $pass
     * @param $domain
     * @return bool
     */
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

    /**
     * @param $isError
     * @param $sql_VirtualUser
     * @param $sql_VirtualAlias
     */
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

    /**
     * @param $isError
     * @param $sql_VirtualAlias
     * @return bool
     */
    public function modifyVirtualAliasSubmethod($isError, $sql_VirtualAlias) {
        if (FALSE == ($isError = $this->_database->state($sql_VirtualAlias))) {
            $Error = $this->_database->commitTransaction();
        }
        return $isError;
    }

    /**
     * @param $isError
     * @param $sql_VirtualUser
     * @param $sql_VirtualAlias
     */
    public function deleteUserSubMethod(&$isError, $sql_VirtualUser, $sql_VirtualAlias) {
        $this->createUserSubMethod($isError, $sql_VirtualUser, $sql_VirtualAlias);
    }

    /**
     * @param $srcUser
     * @param $srcDomain
     * @param $DestEmail
     * @return bool
     */
    public function createAlias($srcUser, $srcDomain, $DestEmail) {
        $srcAddress = $this->realEscape($srcUser . "@" . $srcDomain);
        $destAddress = $this->realEscape($DestEmail);
        $sourceDomain = $this->realEscape($srcDomain);
        $isError = $this->getDomainID($sourceDomain, $domain_id);
        $sql_VirtualAlias = EmailQueries::createForward($srcAddress, $destAddress, $sourceDomain);
        if (!$isError) {
            if (FALSE == ($this->_database->startTransaction())) {

                $isError = $this->modifyVirtualAliasSubmethod($isError, $sql_VirtualAlias);
            } else {
                //Something Bad happened
                $isError = TRUE;
                $Error = $this->_database->cancelTransaction();
            }

            $Error = $this->_database->verifyTransactions();

        }
            return $isError;

    }

    /**
     * @return bool
     */
    public function getEmailOverview() {
        $sortType = $this->_sortType;
        $this->freeResult($this->_emailQueryResult);
        $query = EmailQueries::getOverview() . " ORDER BY " . $sortType;
        return $this->queryDB($this->_emailQueryResult, $query);
    }

    /**
     * @return string
     */
    public function displayEmailOverview() {
        $isError = FALSE;
        $numRows = 0;
        $queryArray = array();
        $myTable = ''
                . '<thead><tr><th colspan="2">Email Address</th><th></th>'
                . '<th colspan="2">Alias Account</th></tr>'
                . '<tr><th class="sort" onclick="submitSortForm(\'SOURCEUSER\', \'Overview\')">User</th><th class="sort" onclick="submitSortForm(\'SOURCEDOMAIN\', \'Overview\')">Domain</th><th></th><th class="sort" onclick="submitSortForm(\'DESTUSER\', \'Overview\')">User</th><th class="sort" onclick="submitSortForm(\'DESTDOMAIN\', \'Overview\')">Domain</th></tr></thead><tbody>';
        if (FALSE !== ($isError = $this->getEmailOverview())) {
            ;
        } elseif (FALSE !== ($isError = $this->_database->getCount($this->_emailQueryResult, $numRows))) {
            ;
        } elseif ($numRows == 0) {
            $myTable .= '<tr class="" >'
                    . '<td class="" colspan="6">No emails created yet. :)</td>'
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

    /**
     * @return string
     */
    public function displayAliases() {
        $isError = FALSE;
        $numRows = 0;
        $queryArray = array();
        $myTable = ''
                . '<thead><tr><th></th><th colspan="2">Email Address</th><th></th>'
                . '<th colspan="2">Alias Account</th></tr>'
                . '<tr><th></th><th class="sort" onclick="submitSortForm(\'SOURCEUSER\', \'Aliases\')'
                . '">User</th><th class="sort" onclick="submitSortForm(\'SOURCEDOMAIN\', '
                . '\'Aliases\')">Domain</th><th></th><th class="sort" onclick="'
                . 'submitSortForm(\'DESTUSER\', \'Aliases\')">User</th>'
                . '<th class="sort" onclick="submitSortForm(\'DESTDOMAIN\', \'Aliases\')">'
                . 'Domain</th></tr></thead><tbody>';
        if (FALSE !== ($isError = $this->getEmailOverview())) {
            ;
        } elseif (FALSE !== ($isError = $this->_database->getCount($this->_emailQueryResult, $numRows))) {
            ;
        } elseif ($numRows == 0) {
            $myTable .= '<tr class="" >'
                    . '<td class="" colspan="6" >No emails created yet. :)</td>'
                    . '</tr>';
        } else {
            $this->_displayAliases($myTable, $isError, $queryArray);
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

    /**
     * @return string
     */
    public function displayAccounts() {
        $isError = FALSE;
        $numRows = 0;
        $queryArray = array();
        $myTable = ''
                . '<thead><tr><th colspan="3">Email Accounts</th>'
                . '</tr>'
                . '<tr><th></th>'
                . '<th class="sort" onclick="submitSortForm(\'SOURCEUSER\', \'Accounts\')">'
                . 'User</th><th class="sort" onclick="submitSortForm(\'SOURCEDOMAIN\', '
                . '\'Accounts\')">Domain</th></tr></thead><tbody>';
        if (FALSE !== ($isError = $this->getEmailOverview())) {
            ;
        } elseif (FALSE !== ($isError = $this->_database->getCount($this->_emailQueryResult, $numRows))) {
            ;
        } elseif ($numRows == 0) {
            $myTable .= '<tr class="" >'
                    . '<td class="" colspan="3">No emails created yet. :)</td>'
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

    /**
     * @return string
     */
    public function displayDomains() {
        $isError = FALSE;
        $numRows = 0;
        $queryArray = array();
        $myTable = ''
                . '<thead><tr><th></th><th onclick="sortTable(1, 1)">Domains</th>'
                . '</tr>'
                . '</thead><tbody>';

        $myOptions = '';
        $result = FALSE;
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

    /**
     * @param $domain
     * @param $id
     * @return bool
     */
    public function getDomainID($domain, &$id) {
        $SqlQuery = "SELECT id FROM `virtual_domains` WHERE `name`='" . $domain . "'";
        $result = array();
        if (FALSE == ($isError = $this->_database->getOneQueryRow($result, $SqlQuery))) {
            $id = $result['id'];
        }
        return $isError;
    }

    /**
     * @param $result
     * @return bool
     */
    Public function getDomains(&$result) {
        $domainQuery = EmailQueries::getDomains();
        return $this->queryDB($result, $domainQuery);
    }

    /**
     * @param $result
     * @return bool
     */
    Public function getAccounts(&$result) {
        $sortType = self::SOURCEDOMAIN;
        $this->freeResult($this->_emailQueryResult);
        $query = EmailQueries::getOverview() . " ORDER BY " . $sortType;
        return $this->queryDB($result, $query);
    }

    /**
     * @return string
     */
    public function getDomainOptions() {
        $isError = FALSE;
        $myOptions = '';
        $result = FALSE;
        $queryArray = array();
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

    /**
     * @return string
     */
    public function getAccountOptions() {
        $isError = FALSE;
        $myOptions = '';
        $result = FALSE;
        $queryArray = array();
        if (FALSE !== ($isError = $this->getAccounts($result))) {
            ;
        } elseif (FALSE !== ($isError = $this->_database->getCount($result, $numRows))) {
            ;
        } elseif ($numRows == 0) {
            $myOptions .= '<option>No Domains Created yet.</option>';
        } else {
            $this->_accountOptions($myOptions, $result, $isError, $queryArray);
        }
        return $myOptions;
    }

    /**
     * @param $result
     */
    public function freeResult(&$result) {
        $this->_database->freeResult($result);
    }

    /**
     * @param $sourceUser
     * @param $sourceDomain
     * @param $destUser
     * @param $destDomain
     * @return bool
     */
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

    /**
     * @param $sortString
     */
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

    /**
     * @param $email
     * @return bool
     */
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

    /**
     * @param $isError
     * @param $query
     */
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

    /**
     * @param $isError
     * @param $query
     */
    protected function _passwordReset(&$isError, $query) {
        $this->_domainSubMethod($isError, $query);
    }

    // PRIVATE METHODS

    /**
     * @param $myTable
     * @param $isError
     * @param $queryArray
     */
    private function _displayAccounts(&$myTable, &$isError, &$queryArray) {
        $SD_class = 'td_alignLeft';
        $UserAcctClass = 'td_alignRight';
        $SU = $SD = $DU = $DD = '';
        $class = "delete";
        while (FALSE === ($isError = $this->_database->fetchArray($this->_emailQueryResult, $queryArray)) && $queryArray != NULL) {
//                if (!$isError) {
//                    var_dump($this->_emailQueryResult, $queryArray);
//                }
            $this->_setQueryResultVars($SU, $SD, $DU, $DD, $queryArray);
            $isAccount = (NULL == $DU);

            if ($isAccount) {
                $myTable .= '<tr>'
                        . '<td class=""><span class="' . $class . '" onclick="'
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

    /**
     * @param $myTable
     * @param $isError
     * @param $queryArray
     */
    private function _displayEmail(&$myTable, &$isError, &$queryArray) {
        $SD_class = 'td_alignLeft';
        $UserAcctClass = 'td_alignRight';
        $account = 'accountRow';
        $accountColspan = 3;
        $alias = 'aliasRow';
        $SU = $SD = $DU = $DD = '';
        while (FALSE === ($isError = $this->_database->fetchArray($this->_emailQueryResult, $queryArray)) && $queryArray != NULL) {
//                if (!$isError) {
//                    var_dump($this->_emailQueryResult, $queryArray);
//                }
            $this->_setQueryResultVars($SU, $SD, $DU, $DD, $queryArray);
            $isAccount = (NULL == $DU);


            $currentRow = '<td class="' . $UserAcctClass . '">' . $SU . '</td>'
                    . '<td class="' . $SD_class . '">@' . $SD . '</td>';
            if ($isAccount) {
                $myTable .= '<tr class="' . $account . '">'
                        . $currentRow
                        . '<td class="center" colspan="' . $accountColspan . '">'
                        . 'Is an account.</td>';
            } else {
                $myTable .= '<tr class="' . $alias . '">'
                        . $currentRow
                        . '<td >Is an Alias of:</td>'
                        . '<td class="' . $UserAcctClass . '">' . $DU . '</td>'
                        . '<td class="' . $SD_class . '">@' . $DD . '</td>';
            }
            $myTable .= '</tr>';
        }
    }

    /**
     * @param $myTable
     * @param $isError
     * @param $queryArray
     */
    private function _displayAliases(&$myTable, &$isError, &$queryArray) {
        $SD_class = 'td_alignLeft';
        $UserAcctClass = 'td_alignRight';
        $alias = 'aliasRow';
        $class = 'delete';
        $SU = $SD = $DU = $DD = '';
        while (FALSE === ($isError = $this->_database->fetchArray($this->_emailQueryResult, $queryArray)) && $queryArray != NULL) {
//                if (!$isError) {
//                    var_dump($this->_emailQueryResult, $queryArray);
//                }
            $this->_setQueryResultVars($SU, $SD, $DU, $DD, $queryArray);
            $isAccount = (NULL == $DU);


            $currentRow = '<td class="' . $UserAcctClass . '">' . $SU . '</td>'
                    . '<td class="' . $SD_class . '">@' . $SD . '</td>';
            if ($isAccount) {
                ;
            } else {
                $myTable .= '<tr class="' . $alias . '">'
                        . '<td class=""><span class="' . $class . '" onclick="'
                        . 'submitAliasDelete(\'' . $SU . '\', \'' . $SD . '\', \'' . $DU . '\', \'' . $DD . '\',\'Aliases\')">'
                        . ' &#xd7;&nbsp;Delete</span></td>'
                        . $currentRow
                        . '<td >Is an Alias of:</td>'
                        . '<td class="' . $UserAcctClass . '">' . $DU . '</td>'
                        . '<td class="' . $SD_class . '">@' . $DD . '</td>';
            }
            $myTable .= '</tr>';
        }
    }

    /**
     * @param $myOptions
     * @param $result
     * @param $isError
     * @param $queryArray
     */
    private function _domainOptions(&$myOptions, $result, &$isError, &$queryArray) {
        while (FALSE === ($isError = $this->_database->fetchArray($result, $queryArray)) && $queryArray != NULL) {
            $myOptions .= '<option value="' . $queryArray["name"] . '">' . $queryArray['name'] . '</option>';
        }
    }

    /**
     * @param $myOptions
     * @param $result
     * @param $isError
     * @param $queryArray
     */
    private function _accountOptions(&$myOptions, $result, &$isError, &$queryArray) {
        $emailDomain = "";
        $SU = $SD = $DU = $DD = '';
        while (FALSE === ($isError = $this->_database->fetchArray($result, $queryArray)) && $queryArray != NULL) {
            $this->_setQueryResultVars($SU, $SD, $DU, $DD, $queryArray);
            $isAccount = (NULL == $DU);
            if ($isAccount) {
                if ($SD != $emailDomain) {
                    if ($emailDomain === "") {
                        $myOptions .= '<optgroup label="' . $SD . '">';
                    } else {
                        $myOptions .= '</optgroup>'
                                . '<optgroup label="' . $SD . '">';
                    }
                    $emailDomain = $SD;
                }
                $myOptions .= '<option value="' . $SU . '@' . $SD . '">' . $SU . '@' . $SD . '</option>';
            }
        }
        $myOptions .= '</optgroup>';
    }

    /**
     * @param $myTable
     * @param $result
     * @param $isError
     * @param $queryArray
     */
    private function _displayDomains(&$myTable, $result, &$isError, &$queryArray) {
        $class = "delete";
        while (FALSE === ($isError = $this->_database->fetchArray($result, $queryArray)) && $queryArray != NULL) {
            $myTable .= '<tr>'
                    . '<td class=""><span class="' . $class . '" onclick="'
                    . 'submitDomainDelete(\'' . $queryArray["name"] . '\', \'Domains\')">'
                    . ' &#xd7;&nbsp;Delete</span></td>'
                    . '<td class="">' . $queryArray["name"] . '</td>'
                    . '</tr>';
        }
    }

    /**
     * @param $SU
     * @param $SD
     * @param $DU
     * @param $DD
     * @param $queryArray
     */
    private function _setQueryResultVars(&$SU, &$SD, &$DU, &$DD, &$queryArray) {
        $DU = $queryArray[$this->_DU];
        $DD = $queryArray[$this->_DD];
        $SU = $queryArray[$this->_SU];
        $SD = $queryArray[$this->_SD];
    }

}
