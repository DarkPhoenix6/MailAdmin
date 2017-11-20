<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EmailQueries
 *
 * @author cfedun
 */
class EmailQueries {

    //put your code here
    //Overview
    static function getOverview() {
        return "SELECT"
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
    }

    // USERS
    static function getUsers() {
        return "SELECT SUBSTR(email, 1, INSTR(email, '@')-1) AS SourceUser"
                . ",SUBSTR(email, INSTR(email, '@')+1) AS SourceDomain FROM `virtual_users`";
    }

    static function getUsersWithAliasCount() {
        $order = "ORDER BY `SourceDomain`ASC, `SourceUser` ASC, `destCount` ASC";
        $order2 = "ORDER BY SourceUser ASC , SourceDomain ASC";
        return "SELECT * FROM "
                . "( SELECT SUBSTR(email, 1, INSTR(email, '@') - 1) AS SourceUser, "
                . "SUBSTR(email, INSTR(email, '@') + 1) AS SourceDomain, email "
                . "FROM `virtual_users` ) AS `users` JOIN "
                . "( SELECT DISTINCT destination, "
                . "COUNT(destination) AS `destCount` "
                . "FROM `virtual_aliases` LEFT JOIN `virtual_users` "
                . "ON `virtual_users`.`email` = `destination` "
                . "GROUP BY `destination` ) AS "
                . "`counted` ON `users`.`email` = `counted`.`destination` "
                . $order;
    }

    static function deleteUser($email) {
        return "DELETE FROM `virtual_users`, `virtual_aliases` USING `virtual_users` INNER JOIN `virtual_aliases` ON `virtual_users`.`email`=`virtual_aliases`.`destination` WHERE `virtual_users`.`email`='" . $email . "'";
    }

    // Forwards / ALIASES

    static function aliasCount() {
        return "SELECT DISTINCT destination, COUNT(destination) AS `destCount` FROM `virtual_aliases` GROUP BY `destination` ";
    }

    static function userAlialCount() {
        return "SELECT DISTINCT destination, COUNT(destination) AS `destCount` FROM `virtual_aliases` LEFT JOIN `virtual_users` ON `virtual_users`.`email`=`destination` GROUP BY `destination` ";
    }

    static function createForward($srcAddress, $destAddress, $srcDomain) {
        $s = "INSERT INTO `virtual_aliases` (`domain_id`, `source`, `destination`) VALUES ( (" . $this->getDomainID($srcDomain) . "), '" . $srcAddress . "', '" . $destAddress . "');";
        return $s;
    }

    // Domains
    static function createDomain($domain) {
        return "INSERT INTO `virtual_domains` (`id`, `name`) VALUES (NULL, '"
                . $domain . "')";
    }

    static function deleteDomain($domain) {
        $sql = "DELETE FROM `virtual_domains` WHERE `name`='" . $domain . "'";
        return $sql;
    }

    static function getDomainID($domain) {
        return "SELECT id FROM `virtual_domains` WHERE `name`='" . $domain . "'";
    }

    static function getDomains() {
        return "SELECT name, id FROM `virtual_domains` ORDER BY name"
                . " ASC";
    }

    static function getDomainsWithCount() {
        return "SELECT domain.name AS Dname, domain.id AS dId, COUNT(vUser.id) AS users_count, (SELECT COUNT(id) FROM `virtual_aliases` WHERE domain_id = domain.id) AS alias_count FROM `virtual_domains` AS domain LEFT JOIN `virtual_users` AS vUser ON (vUser.domain_id = domain.id) GROUP BY domain.id ORDER BY domain.name ASC ";
    }

    // PASSWORDS

    static function generatePassString($realEscapePass) {
        return "CONCAT('{SHA256-CRYPT}', ENCRYPT ('"
                . $realEscapePass
                . "', CONCAT('$5$', SUBSTRING(SHA(RAND()), -16))))";
    }

    static function updatePassword($email, $realEscapePass) {
        return "UPDATE virtual_users SET password="
                . $this->generatePassString($realEscapePass)
                . " WHERE `email`='" . $email . "';";
    }

}
