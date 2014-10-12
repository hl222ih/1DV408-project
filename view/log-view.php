<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class LogView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Log");
    }

    function getHtml() {
        $html = "<p>Content missing...</p>";

        return parent::getSurroundingHtml($html);
    }
} 