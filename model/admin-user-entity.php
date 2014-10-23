<?php

namespace BoostMyAllowanceApp\Model;

class AdminUserEntity {

    private $id;

    private $userId;
    private $adminId;

    private $usersName;
    private $adminsName;

    private $balance;

    public function __construct($id, $userId, $adminId, $usersName, $adminsName, $balance = 5) {
        $this->id = $id;
        $this->userId = $userId;
        $this->adminId = $adminId;
        $this->usersName = $usersName;
        $this->adminsName = $adminsName;
        $this->balance = $balance;
    }

    public function getId() {
        return $this->id;
    }

    public function getUsersName() {
        return $this->usersName;
    }
    public function getAdminsName() {
        return $this->adminsName;
    }

    public function getBalance() {
        return $this->balance;
    }
}