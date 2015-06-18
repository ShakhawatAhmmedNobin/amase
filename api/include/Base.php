<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 18.06.15
 * Time: 12:53
 */
include_once("./db.php");
class Base {

    private $connection;

    function __construct() {
        $this->connection = make_connection();
    }
}