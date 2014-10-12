<?php

namespace BoostMyAllowanceApp\Controller;

require_once("/model/model.php");

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\View\GenericView;

class AuthController {
    private $model;
    private $genericView;
    private $appController;

    public function __construct() {
        $this->model = new Model();
        $this->genericView = new GenericView($this->model);
    }
    public function start() {
        if (!$this->model->isLoggedIn()) {
            $this->model->cookieLogin();
            if (!$this->model->isLoggedIn()) {
                $this->genericView->unsetCookies();
                $this->model->logout(); //and clean session
            }
        }
    }
}