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
        <div class="container">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">' . Model::APP_NAME . '</a>
          </div>
          <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">' .
                (!$this->model->isUserLoggedIn() ? '
              <li ' . (LoginView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '><a href="?page=' . LoginView::getPageName() . '">Inloggning</a></li>
              ' : '') . '
              <li ' . (RegisterView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '><a href="?page=' . RegisterView::getPageName() . '">Registrering</a></li>
              <li ' . (EventsView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '><a href="?page=' . EventsView::getPageName() . '">Händelser</a></li>
              <li ' . (TasksView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '><a href="?page=' . TasksView::getPageName() . '">Uppgifter</a></li>
              <li ' . (TransactionsView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '><a href="?page=' . TransactionsView::getPageName() . '">Överföringar</a></li>
              <li ' . (LogView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '><a href="?page=' . LogView::getPageName() . '">Logg</a></li>
              <li ' . (SettingsView::getClassName() == $this->viewClassName ? 'class="active"' : '') . '><a href="?page=' . SettingsView::getPageName() . '">Inställningar</a></li>' .
                ($this->model->isUserLoggedIn() ? '
              <li><a href="?page=logout">Logga ut</a></li>
              ' : '') . '
            </ul>
            <div><span class="label label-info pull-left">' . ($this->model->isUserLoggedIn() ?
            'Inloggad som ' . $this->model->getUsersName() . ' ' :
            'Ej inloggad') . '</span></div>
          </div>
        </div>
      </div>';
    }
}