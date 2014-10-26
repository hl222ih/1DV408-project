<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\Model\Task;

class TasksView extends View {
    private static $getIsUpcomingKey = "onlyupcoming";

    private $showOnlyUpcomingTasks;
    private $tasks;


    public function __construct(Model $model) {
        parent::__construct($model, "Tasks");

        $this->showOnlyUpcomingTasks = isset($_GET[self::$getIsUpcomingKey]) ? $_GET[self::$getIsUpcomingKey] : false;
        if ($this->showOnlyUpcomingTasks) {
            $this->tasks = $this->model->getUpcomingTasks();
        } else {
            $this->tasks = $this->model->getTasks();
        }

    }

    /**
     * Contains the entire page's html for the current view.
     * Is mainly based on shared html in View that this class has inherited.
     * @return string
     */
    function getHtml() {
        $html =
$this->getHtmlForEventEdit() .
(($this->model->isUserAdmin()) ? '
<form action="' . $_SERVER['PHP_SELF'].'?'.$_SERVER["QUERY_STRING"] . '" method="post">
    <div class="well">
     Skapa ny uppgift:' .
$this->getHtmlForCreateNewEvent() . '
    </div>
</form> ' : '') . '
<div class="panel panel-info">
    <div class="panel-heading">
        <div>
            <h3 class="panel-title">' . (($this->showOnlyUpcomingTasks) ? 'Ej utförda ' : 'Alla ') . 'uppgifter&nbsp;&nbsp;&nbsp;' .
            (($this->showOnlyUpcomingTasks) ?
                '<a href="?page=' . TasksView::getPageName() . '&' . self::$getIsUpcomingKey . '=0"><input type="button" class="btn btn-info btn-sm" value="Visa alla" /></a>' :
                '<a href="?page=' . TasksView::getPageName() . '&' . self::$getIsUpcomingKey . '=1"><button type="button" class="btn btn-info btn-sm">Visa inte gamla</button></a>') .'
            </h3>
        </div>
    </div>
    <div class="panel-body">
        <form action="' . $_SERVER['PHP_SELF'].'?'.$_SERVER["QUERY_STRING"] . '" method="post">
            <div class="list-group">' .
            $this->getHtmlForTaskItems() . '
            </div>
        </form>
    </div>
</div>';

        return parent::getSurroundingHtml($html);
    }

    private function getHtmlForTaskItems() {
        $html = "";

        foreach($this->tasks as $task) {
            $html .= '
            <a href="#" class="list-group-item">
                <h4 class="list-group-item-heading"> ' .

                    $this->getHtmlForEventButtonsOfItem($task) .
                    $task->getTitle() .
                    '<span class="label label-info">' .
                    $task->getRewardValue($this->model->isUserAdmin()) . ' ' . $this->model->getUnit()->getShortName() .
                        (($task->getPenaltyValue($this->model->isUserAdmin()) != 0) ?
                            ' (' .  $task->getPenaltyValue($this->model->isUserAdmin()) . ' ' . $this->model->getUnit()->getShortName() . ')'  :
                            '' )
                    . '</span>' .
                '</h4>' .
                    $this->getHtmlForEventLabelsOfItem($task) . '
            </a>';
        }
        unset($task);

        return $html;
    }
} 