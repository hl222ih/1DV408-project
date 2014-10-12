<?php

namespace BoostMyAllowanceApp\Controller;

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

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\View\GenericView;
use BoostMyAllowanceApp\View\View;
use BoostMyAllowanceApp\View\LoginView;
use BoostMyAllowanceApp\View\EventsView;
use BoostMyAllowanceApp\View\TasksView;

class Controller {

    private $model;
    private $genericView;
    private $view;

    public function __construct() {
        $this->model = new Model();
        $this->genericView = new GenericView($this->model);
    }

    public function start() {
        if (!$this->model->isLoggedIn()) {
            $this->model->cookieLogin(
                $this->genericView->getUsernameFromCookie(),
                $this->genericView->getEncryptedPasswordFromCookie()
            );
        }
        if (!$this->model->isLoggedIn()) {
            if ($this->genericView->wasLoginButtonClicked()) {
                $this->model->login(
                    $this->genericView->getUsername, password, autoLogin);
            }
        }
        if ($this->model->isLoggedIn()) {
            if ($this->model->isAdmin()) {
                $this->view = new EventsView($this->model);
            } else {
                $this->view = new TasksView($this->model);
            }
        } else {
            $this->genericView->unsetCookies();
            $this->model->logout(); //and clean session
            $this->view = new LoginView($this->model);
        }

        echo $this->view->getHtml();

    }
}