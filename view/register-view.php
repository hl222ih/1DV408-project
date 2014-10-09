<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class RegisterView extends View {

    public function __construct(Model $model, $title) {
        parent::__construct($model, $title);
    }

    function getHtml() {
        $html = $this->getFirstPartOfHtml();
        $html .= "<p>Content missing...</p>";
        $html .= $this->getSecondPartOfHtml();

        return $html;
    }
} 