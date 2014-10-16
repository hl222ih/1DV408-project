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
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
            <link rel="stylesheet" type="text/css" href="styles/styles.css" />
        </head>
        ';
    }
}