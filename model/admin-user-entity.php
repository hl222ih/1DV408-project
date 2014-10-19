<?php

namespace BoostMyAllowanceApp\Model;

class AdminUserEntity {

    private $id;

    private $userId;
    private $adminId;

    private $usersName;
    private $adminsName;

    public function __construct($id, $userId, $adminId, $usersName, $adminsName) {
        $this->id = $id;
        $this->userId = $userId;
        $this->adminId = $adminId;
        $this->usersName = $usersName;
        $this->adminsName = $adminsName;
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
}