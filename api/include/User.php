<?php
/**
 * Created by PhpStorm.
 * User: marvin
 * Date: 18.06.15
 * Time: 13:43
 */

class User {

    private $id;
    private $username;
    private $email;
    private $password;
    private $forename;
    private $surname;

    function __construct($values) {
        $this->id = $values[ID];
        $this->username = $values[username];
        $this->email = $values[email];
        $this->password = $values[password];
        $this->forename = $values[forename];
        $this->surname = $values[surname];
    }

}