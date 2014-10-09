<?php

namespace BoostMyAllowanceApp\Controller;

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\View;

require_once("model/model.php");
require_once("view/view.php");
require_once("view/events-view.php");
require_once("view/log-view.php");
require_once("view/login-view.php");
require_once("view/register-view.php");
require_once("view/settings-view.php");
require_once("view/tasks-view.php");
require_once("view/transactions-view.php");

class AppController {

    public function __construct() {
    }
}