<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class LogView extends View {

    public function __construct(Model $model) {
        parent::__construct($model, "Log");
    }

    function getHtml() {
        $html = '
            <div class="panel panel-info">
                <div class="panel-heading">Logg</div>
                    <div class="panel-body">
                        <p>
                            <ul class="list-group"> ' .
                                $this->getHtmlForLogItems() . '
                            </ul>
                        </p>
                    </div>
                </div>
            </div>';

            return parent::getSurroundingHtml($html);
        }

    private function getHtmlForLogItems() {
        $html = '';
        $logItems = $this->model->getLogItems();

        foreach ($logItems as $logItem) {
            $html .= '
                <li class="list-group-item">
                    <span class="label label-default">&nbsp;' .
                        $logItem->getTimeOfLog() .'
                    </span>&nbsp;&nbsp;' .
                    $logItem->getMessage() . '
                </li>';
        }
        return $html;
    }
} 