<?php

namespace BoostMyAllowanceApp\Controller;

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\View\GenericView;
use BoostMyAllowanceApp\View\LoginView;

require_once("/model/model.php");

require_once("/view/generic-view.php");

require_once("/view/view.php");
require_once("/view/events-view.php");
require_once("/view/log-view.php");
require_once("/view/login-view.php");
require_once("/view/register-view.php");
require_once("/view/settings-view.php");
require_once("/view/tasks-view.php");
require_once("/view/transactions-view.php");

class AppController {
    private $model;
    private $genericView;
    private $view;

    public function __construct() {
        $this->model = new Model();
        $this->genericView = new GenericView($this->model);
    }

    public function start() {
        if (true) {
            $this->view = new LoginView($this->model);
        }
        echo $this->view->getHtml();
    }
}