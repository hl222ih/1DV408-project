<?php

namespace BoostMyAllowanceApp\Model;

/**
 * Class AdminUserEntity
 *
 * This class holds the entity that connects a user (child) and its administrator (parent)
 *
 * A user may have more than one parent, and a parent may have more than one child.
 *
 * Each child will have an experience with all parents together.
 * Each parent will have an experience with all children together.
 *
 * For example, when balance is displayed in the view, it will be the total balance of all
 * AdminUserEntities together, connected to that user.
 *
 * The table in the database is called "user_to_user" and is somewhat used interchangably.
 *
 * @package BoostMyAllowanceApp\Model
 */
class AdminUserEntity {

    private $id;

    private $userId;
    private $adminId;

    private $usersName;
    private $adminsName;

    private $balance;

    public function __construct($id, $userId, $adminId, $usersName, $adminsName, $balance) {
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

    public function getUsersId() {
        return $this->userId;
    }

    public function getAdminsId() {
        return $this->adminId;
    }
}