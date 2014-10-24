<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\Model\Task;
use BoostMyAllowanceApp\Model\Transaction;

class EventsView extends View {

    private static $getIsPendingKey = "onlypending";
    private $showOnlyPendingEvents;
    private $events;

    public function __construct(Model $model) {
        parent::__construct($model, "Events");

        $this->showOnlyPendingEvents = isset($_GET[self::$getIsPendingKey]) ? $_GET[self::$getIsPendingKey] : false;
        if ($this->showOnlyPendingEvents) {
            $this->events = $this->model->getPendingEvents();
        } else {
            $this->events = $this->model->getEvents();
        }
    }

    function getHtml() {
$html = $this->getHtmlForEventEdit() . '
<div class="panel panel-info">
    <div class="panel-heading">
        <div>
            <h3 class="panel-title">' . (($this->showOnlyPendingEvents) ? 'Avvaktande u' : 'U') . 'ppgifter och överföringar&nbsp;&nbsp;&nbsp;' .
                    (($this->showOnlyPendingEvents) ?
                    '<a href="?page=' . EventsView::getPageName() . '&' . self::$getIsPendingKey . '=0"><input type="button" class="btn btn-info btn-sm" value="Visa alla" /></a>' :
                    '<a href="?page=' . EventsView::getPageName() . '&' . self::$getIsPendingKey . '=1"><button type="button" class="btn btn-info btn-sm">Visa bara avvaktande</button></a>') .'
            </h3>
        </div>
    </div>
    <div class="panel-body">
        <form action="' . $_SERVER['PHP_SELF'].'?'.$_SERVER["QUERY_STRING"] . '" method="post">
            <div class="list-group">' .
                $this->getHtmlForEventItems() . '
            </div>
        </form>
    </div>
</div>';

        return parent::getSurroundingHtml($html);
    }

    private function getHtmlForEventItems() {
        $html = "";

        foreach($this->events as $event) {
            $html .= '
            <a href="#" class="list-group-item">
                <h4 class="list-group-item-heading">' .

                $this->getHtmlForEventButtonsOfItem($event) .
                $event->getTitle() .
                    '<span class="label label-info">' .
                    (($event->getClassName() == Task::getClassName()) ?
                        $event->getRewardValue(true) . ' ' . $this->model->getUnit()->getShortName() .
                            (($event->getPenaltyValue(true) != 0) ?
                                ' (' .  $event->getPenaltyValue(true) . ' ' . $this->model->getUnit()->getShortName() . ')'  :
                                '' ) :
                                $event->getTransactionValue(true) . ' ' . $this->model->getUnit()->getShortName()) .
                    '</span>' .
                '</h4>' .
                $this->getHtmlForEventLabelsOfItem($event) . '
            </a>
            ';
        }
        unset($event);

        return $html;
    }
}
