<?php

namespace BoostMyAllowanceApp\View;

class Head {

    private $title;

    public function __construct($title) {
        $this->title = $title;
    }

    public function getHtml() {

        return '
        <head>
            <meta charset="utf-8" />
            <title>' . $this->title . '</title>
            <link rel="stylesheet" type="text/css" href="styles/styles.css" />
        </head>
        ';
    }
}