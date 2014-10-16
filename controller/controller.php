<?php

namespace BoostMyAllowanceApp\Controller;

require_once("model/model.php");

require_once("view/view-keys.php");
require_once("view/generic-view.php");

require_once("view/view.php");
require_once("view/events-view.php");
require_once("view/log-view.php");
require_once("view/login-view.php");
require_once("view/register-view.php");
require_once("view/settings-view.php");
require_once("view/tasks-view.php");
require_once("view/transactions-view.php");

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\View\GenericView;
use BoostMyAllowanceApp\View\View;
use BoostMyAllowanceApp\View\LoginView;
use BoostMyAllowanceApp\View\EventsView;
use BoostMyAllowanceApp\View\TasksView;
use BoostMyAllowanceApp\View\LogView;
use BoostMyAllowanceApp\View\SettingsView;
use BoostMyAllowanceApp\View\TransactionsView;
use BoostMyAllowanceApp\View\RegisterView;

class Controller {

    private $model;
    private $genericView;
    private $view;

    static private $logoutViewName = "logout"; //not an actual view -> logged out and redirected to login

    public function __construct() {
        $this->model = new Model();
        $this->genericView = new GenericView($this->model);
    }

    public function start() {

        if (!$this->model->isUserLoggedIn()) {
            $this->model->cookieLogin(
                $this->genericView->getUsernameFromCookie(),
                $this->genericView->getEncryptedPasswordFromCookie()
            );
            if (!$this->model->isUserLoggedIn()) {
                if ($this->genericView->wasLoginButtonClicked()) {
                    $this->model->login(
                        $this->genericView->getUsername(),
                        $this->genericView->getPassword(),
                        $this->genericView->wasAutoLoginChecked()
                    );
                }
            }
            if ($this->model->isUserLoggedIn()) {
                $this->loadOrReloadLoggedInDefault();
            } else {
                $this->genericView->unsetCookies();
                $this->model->logoutUser(); //and clean session
                $requestedPage = $this->genericView->getRequestedPage();
                $this->model->setRequestedPage($requestedPage);
                if ($requestedPage == LoginView::getPageName()) {
                    $this->view = new LoginView($this->model);
                } else {
                    $this->genericView->redirectPage(LoginView::getPageName());
                }
            }
        } else {
            $requestedPage = $this->genericView->getRequestedPage();
            $this->model->setRequestedPage($requestedPage);
            //TODO: validate if user has the rights to view requested page
            switch ($requestedPage) {
                case TasksView::getPageName():
                    $this->view = new TasksView($this->model);
                    break;
                case EventsView::getPageName():
                    if ($this->model->isAdmin()) {
                        $this->view = new EventsView($this->model);
                    } else {
                        $this->loadOrReloadLoggedInDefault();
                    }
                    break;
                case LoginView::getPageName():
                    $this->loadOrReloadLoggedInDefault();
                    break;
                case LogView::getPageName():
                    $this->view = new LogView($this->model);
                    break;
                case SettingsView::getPageName():
                    $this->view = new SettingsView($this->model);
                    break;
                case RegisterView::getPageName():
                    //TODO: log user out first
                    $this->view = new RegisterView($this->model);
                    break;
                case TransactionsView::getPageName():
                    $this->view = new TransactionsView($this->model);
                    break;
                case self::$logoutViewName:
                    $this->model->logoutUser();
                    $this->genericView->redirectPage(LoginView::getPageName());
                    break;
                default:
                $this->loadOrReloadLoggedInDefault();
            }
        }

        echo $this->view->getHtml();
        $this->model->unsetMessage();
    }

    private function loadOrReloadLoggedInDefault() {
        $requestedPage = $this->genericView->getRequestedPage();
        $this->model->setRequestedPage($requestedPage);
        if ($this->model->isAdmin()) {
            if ($requestedPage == EventsView::getPageName()) {
                $this->view = new EventsView($this->model);
            } else {
                $this->genericView->redirectPage(EventsView::getPageName());
            }
        } else {
            if ($requestedPage == TasksView::getPageName()) {
                $this->view = new TasksView($this->model);
            } else {
                $this->genericView->redirectPage(TasksView::getPageName());
            }
        }
    }
}