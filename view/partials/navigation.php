<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class Navigation {
    private $model;

    private $showAdminItems;
    private $viewClassName;

    public function __construct(Model $model, $viewClassName) {
        $this->model = $model;
        $this->viewClassName = $viewClassName;
    }

    public function getHtml() {
        return '
    <div class="navbar navbar-default">
        <span class="label label-info pull-right">' . ($this->model->isUserLoggedIn() ?
            'Inloggad som ' . $this->model->getUsersName() . ' ' :
            'Ej inloggad') . '</span>' .
        ($this->model->isUserLoggedIn() ?
            '<span class="label label-info pull-right">' .
            'Saldo: ' . $this->model->getTotalBalance()  : '') .
            '</span>
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                </button>
                <a class="navbar-brand" href="?page=' . LoginView::getPageName() . '">' . Model::APP_NAME . '</a>
            </div>
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">' .
                    (!$this->model->isUserLoggedIn() ? '
                    <li ' . (LoginView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '>
                        <a href="?page=' . LoginView::getPageName() . '">
                            <span class="glyphicon glyphicon-log-in"></span> Inloggning
                        </a>
                    </li>
                    ' : '') .
                    (!$this->model->isUserLoggedIn() ? '
                    <li ' . (RegisterView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '>
                        <a href="?page=' . RegisterView::getPageName() . '">
                            <span class="glyphicon glyphicon-asterisk"></span> Registrering
                        </a>
                    </li>
                    ' : '') .
                    ($this->model->isUserLoggedIn() && $this->model->isUserAdmin() ? '
                    <li ' . (EventsView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '>
                        <a href="?page=' . EventsView::getPageName() . '">
                            <span class="glyphicon glyphicon-ok    "></span> Händelser
                        </a>
                    </li>
                    ' : '') .
                    ($this->model->isUserLoggedIn() ? '
                    <li ' . (TasksView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '>
                        <a href="?page=' . TasksView::getPageName() . '">
                            <span class="glyphicon glyphicon-star"></span> Uppgifter
                        </a>
                    </li>
                    <li ' . (TransactionsView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '>
                        <a href="?page=' . TransactionsView::getPageName() . '">
                            <span class="glyphicon glyphicon-usd"></span> Överföringar
                        </a>
                    </li>
                    <li ' . (LogView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '>
                        <a href="?page=' . LogView::getPageName() . '">
                            <span class="glyphicon glyphicon-pencil"></span> Logg
                        </a>
                    </li>
                    <li ' . (SettingsView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '>
                        <a href="?page=' . SettingsView::getPageName() . '">
                            <span class="glyphicon glyphicon-cog"></span>  Inställningar
                        </a>
                    </li>
                    <li>
                        <a href="?page=logout">
                            <span class="glyphicon glyphicon-log-out"></span> Logga ut
                        </a>
                    </li>
                    ' : '') . '
                </ul>
            </div>
        </div>
    </div>';
    }
}