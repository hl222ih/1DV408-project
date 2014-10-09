<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

abstract class View {

    private $title;
    protected $model;

    protected function __construct(Model $model, $title) {
        $this->model = $model;
        $this->title = $title;
    }

    abstract function getHtml();

    protected function getFirstPartOfHtml() {
        $head = new Head($this->title);
        $navigation = new Navigation($this->model, get_class($this));

        $html = '<!DOCTYPE html>' . PHP_EOL;
        $html .= $head->getHtml();
        $html .= '<body>' . PHP_EOL;
        $html .= $navigation->getHtml();

        return $html;
    }

    protected function getSecondPartOfHtml() {
        $footer = new Footer();

        $html = $footer->getHtml();
        $html .= '</body>' . PHP_EOL;

        return $html;
    }
}