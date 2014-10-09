<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class Navigation {

    private $showAdminItems;
    private $viewClassName;

    public function __construct(Model $model, $viewClassName) {
        $this->showAdminItems = $model->getUser()->isAdmin();
    }

    public function getHtml() {
        return '
        <nav>
            <p>Navigation is not implemented...</p>
        </nav>
        ';
    }
}