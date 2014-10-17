<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class LoginView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Login");
    }

    function getHtml() {
        $html = ($this->model->hasMessage() ? '<div class="alert alert-' . $this->getAlertCssClass($this->model->getMessage()->getMessageType()) . '" role="alert">
        ' . $this->model->getMessage()->getMessageText() . '</div>' : '')
            . '
            <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title">Skriv in användarnamn och lösenord</h3>
            </div>
            <div class="panel-body">
            <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div class="form-group">
                    <label for="usernameId">Användarnamn:</label>
                    <input class="form-control" type="text" name="' . self::$postUsernameKey . '" id="usernameId"
                    value="' . $this->model->getLastPostedUsername() . '" autofocus />
                </div>
                <div class="form-group">
                    <label for="passwordId">Lösenord:</label>
                    <input class="form-control" type="password" name="' . self::$postPasswordKey . '" id="passwordId" />
                </div>
                <div class="checkbox">


                    <label><input type="checkbox" name="' . self::$postAutoLoginCheckedKey . '" id="autoLoginId"' .
            (isset($_POST[self::$postAutoLoginCheckedKey]) ? "checked" : "") . ' />Håll mig inloggad</label>
                </div>
                <input type="submit" class="btn btn-primary pull-right" name="' . self::$postLoginButtonNameKey . '" value="Logga in" />

            </form>
            </div>
            </div>
            <p></p>';
        return parent::getSurroundingHtml($html);
    }
}