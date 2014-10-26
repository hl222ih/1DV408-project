<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class RegisterView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Register");
    }

    /**
     * Contains the entire page's html for the current view.
     * @return string
     */
    function getHtml() {
        $html = '
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Fyll i användaruppgifter</h3>
    </div>
    <div class="panel-body">
        <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div class="form-group">
                <label for="nameId">Namn:</label>
                <input class="form-control"
                    type="text"
                    name="' . self::$postNameKey . '"
                    id="nameId"
                    value="' . $this->model->getLastPostedName() . '" autofocus
                    required/>
            </div>
            <div class="form-group">
                <label for="usernameId">Användarnamn:</label>
                <input class="form-control"
                    type="text"
                    name="' . self::$postUsernameKey . '"
                    id="usernameId"
                    value="' . $this->model->getLastPostedUsername() . '"
                    required/>
            </div>
            <div class="form-group">
                <label for="passwordId">Lösenord:</label>
                <input class="form-control"
                    type="password"
                    name="' . self::$postPasswordKey . '"
                    id="passwordId"
                    required/>
            </div>
            <div class="form-group">
                <label for="passwordAgainId">Lösenord (igen):</label>
                <input class="form-control"
                    type="password"
                    name="' . self::$postPasswordAgainKey . '"
                    id="passwordAgainId"
                    required/>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox"
                    name="' . self::$postAdminAccountCheckedKey . '"
                    id="adminAccountId"' .
                        ($this->model->getLastPostedRegisterAdminAccountChecked() ? "checked" : "") . ' />
                    Skapa administratörskonto
                </label>
            </div>
            <input type="submit"
                class="btn btn-primary pull-right"
                name="' . self::$postRegisterButtonNameKey . '"
                value="Registrera" />
        </form>
    </div>
</div>';

        return parent::getSurroundingHtml($html);
    }
} 