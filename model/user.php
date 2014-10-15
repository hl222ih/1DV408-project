<?php

namespace BoostMyAllowanceApp\Model;

use BoostMyAllowanceApp\Dal\Dal;

class User {

    private $id;

    private $isAdmin;

    private $name;
    private $username;
    private $mappedUserIds;

    public function __construct(Dal $dal, $username) {

        $info = $dal->getUserInfo($username);

        $this->id = $info["id"];
        $this->isAdmin = $info["isAdmin"];
        $this->name = $info["name"];
        $this->username = $username;
        $this->mappedUserIds = $info["mappedUserIds"];
    }

    public function isAdmin() {
        return $this->isAdmin;
    }
}