<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

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

    function getHtml() {
        $html = '
<div class="panel panel-info">
    <div class="panel-heading">
        <div>
            <h3 class="panel-title">' . (($this->showOnlyUpcomingTasks) ? 'Ej utförda ' : 'Alla ') . 'uppgifter&nbsp;&nbsp;&nbsp;' .
            (($this->showOnlyUpcomingTasks) ?
                '<a href="?page=' . TasksView::getPageName() . '&' . self::$getIsUpcomingKey . '=0"><input type="button" class="btn btn-info btn-sm" value="Visa alla" /></a>' :
                '<a href="?page=' . TasksView::getPageName() . '&' . self::$getIsUpcomingKey . '=1"><button type="button" class="btn btn-info btn-sm">Visa bara kommande</button></a>') .'
            </h3>
        </div>
    </div>
    <div class="panel-body">
        <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div class="list-group">' .
            $this->getHtmlForTaskLines() . '
            </div>
        </form>
    </div>
</div>';

        return parent::getSurroundingHtml($html);
    }

    private function getHtmlForTaskLines() {
        $html = "";

        foreach($this->tasks as $task) {
            $html .= '
            <a href="#" class="list-group-item">
                <h4 class="list-group-item-heading"> ' .
                    (($this->model->isUserAdmin()) ? '
                        <input type="submit"
                            class="btn btn-danger pull-right"
                            name="' . self::$postRemoveTaskButtonNameKey . '"
                            value="Radera" />' : '') .
                    (($this->model->isUserAdmin()) ? '
                        <input type="submit"
                            class="btn btn-info pull-right"
                            name="' . self::$postEditTaskButtonNameKey . '"
                            value="Redigera" />' : '') .
                    (($task->getIsPending() && $this->model->isUserAdmin()) ? '
                        <input type="submit"
                            class="btn btn-success pull-right"
                            name="' . self::$postConfirmTaskDoneButtonNameKey . '"
                            value="Godkänn" />' : '') .
                    ((!$task->getIsRequested()) ? '
                        <input type="submit"
                            class="btn btn-success pull-right"
                            name="' . self::$postMarkTaskDoneButtonNameKey . '"
                            value="Markera som utförd" />' : '') .
                    (($task->getIsPending()) ? '
                        <input type="submit"
                            class="btn btn-danger pull-right"
                            name="' . self::$postRegretMarkTaskDoneButtonNameKey . '"
                            value="Markera som ej utförd" />' : '') .
                    $task->getTitle() .
                        '<span class="label label-info">' .
                        $task->getRewardValue() . ' ' . $this->model->getUnit()->getShortName() .
                            (($task->getPenaltyValue() != 0) ?
                                ' (' .  $task->getPenaltyValue() . ' ' . $this->model->getUnit()->getShortName() . ')'  :
                                '' )
                        . '</span>' .
                '</h4>
                <p>
                <span class="label label-info">' .
                        $this->model->getChildsName($task->getAdminUserEntityId()) . '
                    </span>
                    <span class="label label-info">
                        Giltig: 2014-02-24 20:30 - 2014-02-25 20:30
                    </span>
                    <span class="label label-info">' .
                        (($task->getIsRequested()) ?
                            'Utförd: ' . $this->formatTimestamp($task->getTimeOfRequest()) :
                            'Ej utförd') . '
                    </span>
                </p>
                <p class="list-group-item-text">
                    <span class="label label-' . (($task->getIsConfirmed()) ? 'success' : (($task->getIsDenied()) ? 'danger' : (($task->getIsPending()) ? 'warning' : 'info'))) . ' pull-left">' .
                $task->getStatusText()
                . '</span>&nbsp;<span>
                    ' . $task->getDescription() . '</span>
                </p>
            </a>';
        }
        unset($task);

        return $html;
    }
} 