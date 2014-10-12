<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class TransactionsView extends View {


    public function __construct(Model $model) {
        parent::__construct($model, "Transactions");
    }

    function getHtml() {
        $html = "<p>Content missing...</p>";

        return parent::getSurroundingHtml($html);
    }
} 