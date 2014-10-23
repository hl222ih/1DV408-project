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
        $html = '
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
        <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div class="list-group">' .
                $this->getHtmlForEventLines() . '
            </div>
        </form>
    </div>
</div>';

        return parent::getSurroundingHtml($html);
    }

    private function getHtmlForEventLines() {
        $html = "";

        foreach($this->events as $event) {
            $html .= '
            <a href="#" class="list-group-item">
                <h4 class="list-group-item-heading">' .

                $this->getHtmlForEventButtonsOfItems($event) .

                $event->getTitle() .
                    '<span class="label label-info">' .
                    (($event->getClassName() == Task::getClassName()) ?
                        $event->getRewardValue() . ' ' . $this->model->getUnit()->getShortName() .
                            (($event->getPenaltyValue() != 0) ?
                                ' (' .  $event->getPenaltyValue() . ' ' . $this->model->getUnit()->getShortName() . ')'  :
                                '' ) :
                                $event->getTransactionValue() . ' ' . $this->model->getUnit()->getShortName()) .
                    '</span>' .
                '</h4>
                <p><span class="label label-info">' .
                $this->model->getChildsName($event->getAdminUserEntityId())
                . '</span>' .
                (($event->getClassName() == Task::getClassName()) ?
                    ' <span class="label label-info">' .
                    'Giltig: 2014-02-24 20:30 - 2014-02-25 20:30</span>' : '') .
                (($event->getClassName() == Task::getClassName()) ?
                    ' <span class="label label-info">' .
                    (($event->getIsRequested()) ?
                        'Utförd: ' . $this->formatTimestamp($event->getTimeOfRequest())
                        : 'Ej utförd') . '</span>' : '') .
                (($event->getClassName() == Transaction::getClassName()) ?
                    ' <span class="label label-info">' .
                    (($event->getHasResponse() && $event->getIsConfirmed()) ?
                        'Överföringen gjordes: ' . $this->formatTimestamp($event->getTimeOfResponse())
                        : 'Förfrågan gjordes: ' . $this->formatTimestamp($event->getTimeOfRequest())) . '</span>' : '') . '
                </p>
                <p class="list-group-item-text">
                    <span class="label label-' . (($event->getIsConfirmed()) ? 'success' : (($event->getIsDenied()) ? 'danger' : (($event->getIsPending()) ? 'warning' : 'info'))) . ' pull-left">' .
                        $event->getStatusText() . (($event->getIsConfirmed() || $event->getIsDenied()) ? ': ' . $event->getValue(true) . ' ' . $this->model->getUnit()->getShortName() : '')
                    . '</span>&nbsp;<span>
                    ' . $event->getDescription() . '</span>
                </p>
            </a>
            ';
        }
        unset($event);

        return $html;
    }
}
