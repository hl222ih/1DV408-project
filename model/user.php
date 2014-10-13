<?php

namespace BoostMyAllowanceApp\Model;


class User {

    private $id;

    private $isAdmin;

    private $name;
    private $username;
    //private $secretToken;
    //private $saltedPassword;
    //private $salt;
    //private $cookiePassword;
    //private $cookieExpiration;
    private $mappedUserId;

    public function __construct($username) {
        //TODO: get info from db
        $this->isAdmin = false; //temp... TODO: check if user is admin
    }

    public function isAdmin() {
        return $this->isAdmin;
    }
}