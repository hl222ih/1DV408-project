<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class SettingsView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Settings");
    }

    function getHtml() {
        $html = '
<div class="panel panel-info">
    <div class="panel-heading">
        <div>
            <h3 class="panel-title">Koppla ihop konton</h3>
        </div>
    </div>
    <div class="panel-body">
        <form action="' . $_SERVER['PHP_SELF'].'?'.$_SERVER["QUERY_STRING"] . '" method="post">
            <span class="label label-info">Ditt användarnamn: ' . $this->model->getUsersUsername() . '</span>
            <span class="label label-info">Ditt token: ' . $this->model->getSecretToken() . '</span>
            <div class="form-group">
                <label for="usernameId">Annat kontos användarnamn</label>
                <input class="form-control"
                    type="text"
                    name="' . self::$postConnectAccountUsernameKey . '"
                    id="usernameId"
                    required/>
            </div>
            <div class="form-group">
                <label for="usernameId">Annat kontos token</label>
                <input class="form-control"
                    type="number"
                    name="' . self::$postConnectAccountTokenKey . '"
                    id="usernameId"
                    required/>
            </div>
            <button type="submit"
                class="btn btn-success pull-right"
                name="' . self::$postConnectAccountsButtonNameKey . '"
                value="-">Koppla ihop konton</button>
        </form>
    </div>
</div>';

        return parent::getSurroundingHtml($html);
    }
} 