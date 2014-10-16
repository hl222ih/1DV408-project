<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class Navigation {
    private $model;

    private $showAdminItems;
    private $viewClassName;

    public function __construct(Model $model, $viewClassName) {
        $this->model = $model;
        preg_match("/\\\\(\w*)View$/", $viewClassName, $matches);

        $this->viewClassName = $matches[1];
        //$this->showAdminItems = $model->getUser()->isAdmin();
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
            <ul class="nav navbar-nav">
              <li ' . ('Login' == $this->viewClassName ? 'class="active"' : '') . '><a href="#">Logga in</a></li>
              <li ' . ('Register' == $this->viewClassName ? 'class="active"' : '') . '><a href="#">Registrera ny användare</a></li>
              <li ' . ('Events' == $this->viewClassName ? 'class="active"' : '') . '><a href="#">Händelser</a></li>
              <li ' . ('Tasks' == $this->viewClassName ? 'class="active"' : '') . '><a href="#">Uppgifter</a></li>
              <li ' . ('Transactions' == $this->viewClassName ? 'class="active"' : '') . '><a href="#">Transaktioner</a></li>
              <li ' . ('Log' == $this->viewClassName ? 'class="active"' : '') . '><a href="#">Logg</a></li>
              <li ' . ('Settings' == $this->viewClassName ? 'class="active"' : '') . '><a href="#">Inställningar</a></li>
              <li ' . ('' == $this->viewClassName ? 'class="active"' : '') . '><a href="#">Logga ut</a></li>
            </ul>
            <div><span class="label label-info pull-left">' . ($this->model->isUserLoggedIn() ?
            'Inloggad som ' . $this->model->getUsersName() . ' ' :
            'Du är inte inloggad.') . '</span></div>
          </div>
        </div>
      </div>';
    }
}