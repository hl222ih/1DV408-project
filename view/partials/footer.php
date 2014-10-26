<?php

namespace BoostMyAllowanceApp\View;

class Footer {

    public function __construct() {
        //
    }

    public function getHtml() {
        return '
        <footer class="well well-sm">
            Hannes Ljusås (hl222ih) - 1DV408 projekt - lnu.se
        </footer>
        <script src="vendors/rome.min.js"></script>
        <script src="scripts/script.js"></script>
        ';
    }
}