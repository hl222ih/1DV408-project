<?php

namespace BoostMyAllowanceApp\Model;

class User {

    private static $sessionIsLoggedInKey = "User::IsLoggedIn";

    private $id;

    private $isAdmin;

    private $name;
    private $username;
    private $mappedUsersIds;


    //--- from session
    private $isLoggedIn;

    public function __construct($username, $id, $isAdmin, $name, $mappedUserIds) {

        $this->username = $username;
        $this->id = $id;
        $this->isAdmin = $isAdmin;
        $this->name = $name;
        $this->mappedUsersIds = $mappedUserIds;

        $this->isLoggedIn = isset($_SESSION[self::$sessionIsLoggedInKey]) ? $_SESSION[self::$sessionIsLoggedInKey] : false;
    }

    public function getIsAdmin() {
        return $this->isAdmin;
    }

    public function setIsAdmin($isAdmin) {
        $this->isAdmin = $isAdmin;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getName() {
        return $this->name;
    }

    public function isLoggedIn() {
        return $this->isLoggedIn;
    }

    public function setLoggedIn($isLoggedIn) {
        if ($isLoggedIn) {
            $_SESSION[self::$sessionIsLoggedInKey] = true;
        } else {
            unset($_SESSION[self::$sessionIsLoggedInKey]);
        }
        $this->isLoggedIn = $isLoggedIn;
    }

    public function getId() {
        return $this->id;
    }

    public function getMappedUsersIds() {
        return $this->mappedUsersIds;
    }
}