<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class RegisterView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Register");
    }

    function getHtml() {
        $html = "<p>Content missing...</p>";

        return parent::getSurroundingHtml($html);
    }
} 