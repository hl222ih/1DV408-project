<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class EventsView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Events");
    }

    function getHtml() {
        $html = "<p>Content missing...</p>";

        return parent::getSurroundingHtml($html);
    }
} 