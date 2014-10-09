<?php

namespace BoostMyAllowanceApp\Model;


class User {

    private $isAdmin;
    
    public function __construct() {
        $this->isAdmin = false; //TODO: check if user is admin
    }

    public function isAdmin() {
        return $this->isAdmin;
    }
} 