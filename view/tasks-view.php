<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class TasksView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Tasks");
    }

    function getHtml() {
        $html = "<p>Content missing...</p>";

        return parent::getSurroundingHtml($html);
    }
} 