<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class LoginView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Login");
    }

    function getHtml() {
        $html = "<p>Content missing...</p>";
        return parent::getSurroundingHtml($html);
    }
}