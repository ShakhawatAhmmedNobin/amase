<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 18.06.15
 * Time: 12:53
 */
include_once("./include/db.php");
include_once("./include/User.php");

class Base {

    private $connection;

    function __construct() {
        $this->connection = make_connection();
        $this->connection->query("SET NAMES 'utf8';");
        $this->connection->query("SET CHARACTER SET utf8;");
        $this->connection->set_charset('utf8');
    }

    function getUser($id){
        $stmt = $this->connection->prepare("SELECT * FROM `amasebase_users` WHERE `ID`=?;");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return new User($res->fetch_assoc());
    }

    function getUsername($id){
        $stmt = $this->connection->prepare("SELECT username FROM `amasebase_users` WHERE `ID`=?;");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc()[username];
    }
}