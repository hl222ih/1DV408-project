<?php

namespace BoostMyAllowanceApp\Model;

use BoostMyAllowanceApp\PDO\PDO;

require_once("pdo/pdo.php");
require_once("model/admin-user-entity.php");
require_once("model/event.php");
require_once("model/log-item.php");
require_once("model/task.php");
require_once("model/transaction.php");
require_once("model/unit.php");
require_once("model/user.php");

class Model {


    public function __construct() {
        $this->user = new User();
    }

    public function getUser() {
        return $this->user;
    }
}