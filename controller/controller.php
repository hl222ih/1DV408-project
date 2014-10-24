<?php

namespace BoostMyAllowanceApp\Controller;

require_once("model/model.php");

require_once("view/view-keys.php");
require_once("view/start-view.php");

require_once("view/view.php");
require_once("view/events-view.php");
require_once("view/log-view.php");
require_once("view/login-view.php");
require_once("view/register-view.php");
require_once("view/settings-view.php");
require_once("view/tasks-view.php");
require_once("view/transactions-view.php");

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\View\StartView;
use BoostMyAllowanceApp\View\View;
use BoostMyAllowanceApp\View\LoginView;
use BoostMyAllowanceApp\View\EventsView;
use BoostMyAllowanceApp\View\TasksView;
use BoostMyAllowanceApp\View\LogView;
use BoostMyAllowanceApp\View\SettingsView;
use BoostMyAllowanceApp\View\TransactionsView;
use BoostMyAllowanceApp\View\RegisterView;
use BoostMyAllowanceApp\View\ViewKeys;

class Controller {

    private $model;
    private $startView;
    private $view;

    static private $logoutViewName = "logout"; //not an actual view -> logged out and redirected to login

    public function __construct() {
        $this->model = new Model();
        $this->startView = new StartView($this->model);
    }

    public function start() {

        if (!$this->model->isUserLoggedIn()) {
            $this->model->cookieLogin(
                $this->startView->getUsernameFromCookie(),
                $this->startView->getEncryptedPasswordFromCookie()
            );
            if (!$this->model->isUserLoggedIn()) {
                if ($this->startView->wasLoginButtonClicked()) {
                    $this->model->login(
                        $this->startView->getUsername(),
                        $this->startView->getPassword(),
                        $this->startView->wasAutoLoginChecked()
                    );
                } else if ($this->startView->wasRegisterButtonClicked()) {
                    if ($this->model->registerNewUser(
                        $this->startView->getUsername(),
                        $this->startView->getPassword(),
                        $this->startView->getPasswordAgain(),
                        $this->startView->getName(),
                        $this->startView->wasCreateAdminAccountChecked()
                    )) {
                        $this->startView->redirectPage(LoginView::getPageName());
                    } else {
                        $this->startView->redirectPage(RegisterView::getPageName());
                    };
                }
            }
            if ($this->model->isUserLoggedIn()) {
                $this->loadOrReloadLoggedInDefault();
            } else {
                $this->startView->unsetCookies();
                $this->model->logoutUser(); //and clean session
                $requestedPage = $this->startView->getRequestedPage();
                $this->model->setRequestedPage($requestedPage);
                if ($requestedPage == LoginView::getPageName()) {
                    $this->view = new LoginView($this->model);
                } else if ($requestedPage == RegisterView::getPageName()) {
                    $this->view = new RegisterView($this->model);
                } else {
                    $this->startView->redirectPage(LoginView::getPageName());
                }
            }
        } else {
            if ($this->startView->wasConfirmTaskDoneButtonClicked()) {
                $this->model->confirmTaskDone($this->startView->getEventId());
            } else if ($this->startView->wasEditTaskButtonClicked()) {
                //$this->model->editTask($this->startView->getEventId());
            } else if ($this->startView->wasRemoveTaskButtonClicked()) {
                $this->model->removeTask($this->startView->getEventId());
            } else if ($this->startView->wasRegretMarkTaskDoneButtonClicked()) {
                $this->model->regretMarkTaskDone($this->startView->getEventId());
            } else if ($this->startView->wasMarkTaskDoneButtonClicked()) {
                $this->model->markTaskDone($this->startView->getEventId());
            } else if ($this->startView->wasConfirmTransactionButtonClicked()) {
                $this->model->confirmTransaction($this->startView->getEventId());
            } else if ($this->startView->wasEditTransactionButtonClicked()) {
                //$this->model->editTransaction($this->startView->getEventId());
            } else if ($this->startView->wasRegretTransactionButtonClicked()) {
                $this->model->regretTransaction($this->startView->getEventId());
            } else if ($this->startView->wasRemoveTransactionButtonClicked()) {
                $this->model->removeTransaction($this->startView->getEventId());
            } else if ($this->startView->wasChangeAdminUserEntityButtonClicked()) {
                $this->model->changeActiveAdminUserEntityId($this->startView->getAdminUserEntityId());
            } else if ($this->startView->wasConnectAccountsButtonClicked()) {
                $this->model->connectAccounts($this->startView->getConnectAccountName(), $this->startView->getConnectAccountToken());
            }

            $requestedPage = $this->startView->getRequestedPage();
            $this->model->setRequestedPage($requestedPage);
            switch ($requestedPage) {
                case TasksView::getPageName():
                    $this->view = new TasksView($this->model);
                    break;
                case EventsView::getPageName():
                    if ($this->model->isUserAdmin()) {
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
                    $this->view = new RegisterView($this->model);
                    break;
                case TransactionsView::getPageName():
                    $this->view = new TransactionsView($this->model);
                    break;
                case self::$logoutViewName:
                    $this->model->logoutUser();
                    $this->startView->redirectPage(LoginView::getPageName());
                    break;
                default:
                $this->loadOrReloadLoggedInDefault();
            }
        }

        echo $this->view->getHtml();
        $this->model->unsetMessage();
    }

    private function loadOrReloadLoggedInDefault() {
        $requestedPage = $this->startView->getRequestedPage();
        $this->model->setRequestedPage($requestedPage);
        if ($this->model->isUserAdmin()) {
            if ($requestedPage == EventsView::getPageName()) {
                $this->view = new EventsView($this->model);
            } else {
                $this->startView->redirectPage(EventsView::getPageName());
            }
        } else {
            if ($requestedPage == TasksView::getPageName()) {
                $this->view = new TasksView($this->model);
            } else {
                $this->startView->redirectPage(TasksView::getPageName());
            }
        }
    }
}