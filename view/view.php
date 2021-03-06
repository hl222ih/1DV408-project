<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\MessageType;
use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\Model\Task;
use BoostMyAllowanceApp\Model\Transaction;
use BoostMyAllowanceApp\Model\Event;

require_once("partials/head.php");
require_once("partials/footer.php");
require_once("partials/navigation.php");

/**
 * Class View
 *
 * Abstract class that should be used by nearly all views (not StartView which is only used for input from POST:s)
 * @package BoostMyAllowanceApp\View
 */
abstract class View extends ViewKeys {

    private $title;
    protected $model;

    public function __construct(Model $model, $viewTitle = "") {
        $this->model = $model;
        $this->title = $model::APP_NAME . " " . $viewTitle;
    }

    protected function getSurroundingHtml($bodyHtml) {
        $html = $this->getFirstPartOfHtml();
        $html .= $bodyHtml;
        $html .= $this->getSecondPartOfHtml();

        return $html;
    }

    private function getFirstPartOfHtml() {
        $head = new Head($this->title);
        $navigation = new Navigation($this->model, get_class($this));

        $html = '<!DOCTYPE html>' . PHP_EOL;
        $html .= $head->getHtml();
        $html .= '<body>' . PHP_EOL;
        $html .= $navigation->getHtml();
        $html .= ($this->model->hasMessage() ? '<div class="alert alert-' .
            $this->getAlertCssClass($this->model->getMessage()->getMessageType()) .
            '" role="alert"><a href="#" class="close" data-dismiss="alert">&times;</a>
        ' . $this->model->getMessage()->getMessageText() . '</div>' : '');

        return $html;
    }

    private function getSecondPartOfHtml() {
        $footer = new Footer();

        $html = $footer->getHtml();
        $html .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>';
        $html .= '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>';
        $html .= '</body>' . PHP_EOL;

        return $html;
    }

    public static function getClassName() {
        return get_called_class();
    }

    public static function getPageName() {
        preg_match("/\\\\(\w*)View$/", get_called_class(), $matches);
        return lcfirst($matches[1]);
    }

    public function getAlertCssClass($messageType) {

        switch ($messageType) {
            case MessageType::Info:
                $alertCssClass = "info";
                break;
            case MessageType::Success:
                $alertCssClass = "success";
                break;
            case MessageType::Error:
                $alertCssClass = "danger";
                break;
            case MessageType::Warning:
                $alertCssClass = "warning";
                break;
            default:
                $alertCssClass = "default";
        }

        return $alertCssClass;
    }

    protected function formatTimestamp($timestamp) {
        return date("Y-m-d H:i", $timestamp);
    }

    protected function getHtmlForEventButtonsOfItem(Event $event) {
        $html = '';

        if ($event->getClassName() == Task::getClassName()) {

            $html =
                (($this->model->isUserAdmin()) ? '
                <button type="submit"
                    class="btn btn-danger pull-right"
                    name="' . self::$postRemoveTaskButtonNameKey . '"
                    value="' . $event->getId() . '">Radera</button>' : '') .
                (($this->model->isUserAdmin()) ? '
                <button type="submit"
                    class="btn btn-info pull-right"
                    name="' . self::$postEditTaskButtonNameKey . '"
                    value="' . $event->getId() . '">Redigera</button>' : '') .
                (($event->getIsPending() && $this->model->isUserAdmin()) ? '
                <button type="submit"
                    class="btn btn-success pull-right"
                    name="' . self::$postConfirmTaskDoneButtonNameKey . '"
                    value="' . $event->getId() . '">Godkänn</button>' : '') .
                ((!$event->getIsRequested()) ? '
                <button type="submit"
                    class="btn btn-success pull-right"
                    name="' . self::$postMarkTaskDoneButtonNameKey . '"
                    value="' . $event->getId() . '">Markera som utförd</button>' : '') .
                (($event->getIsPending()) ? '
                <button type="submit"
                    class="btn btn-danger pull-right"
                    name="' . self::$postRegretMarkTaskDoneButtonNameKey . '"
                    value="' . $event->getId() . '">Markera som ej utförd</button>' : '');
        } else if ($event->getClassName() == Transaction::getClassName()) {
            $html =
                (($this->model->isUserAdmin() && $event->getIsPending()) ? '
                <button type="submit"
                class="btn btn-success pull-right"
                name="' . self::$postConfirmTransactionButtonNameKey . '"
                value="' . $event->getId() . '">Godkänn</button>' : '') .
                (($event->getIsPending()) ? '
                <button type="submit"
                    class="btn btn-info pull-right"
                    name="' . self::$postEditTransactionButtonNameKey . '"
                    value="' . $event->getId() . '">Redigera</button>' : '') .
                (($event->getIsPending() && !$this->model->isUserAdmin()) ? '
                <button type="submit"
                    class="btn btn-warning pull-right"
                    name="' . self::$postRegretTransactionButtonNameKey . '"
                    value="' . $event->getId() . '">Ångra</button>' : '') .
                (($event->getIsPending() && $this->model->isUserAdmin()) ? '
                <button type="submit"
                    class="btn btn-danger pull-right"
                    name="' . self::$postRemoveTransactionButtonNameKey . '"
                    value="' . $event->getId() . '">Radera</button>' : '');
        }
        return $html;
    }

    protected function getHtmlForEventLabelsOfItem(Event $event) {
        $html =
        '<p>
            <span class="label label-info">' .
            (($this->model->isUserAdmin()) ?
                $this->model->getChildsName($event->getAdminUserEntityId()) :
                $this->model->getParentsName($event->getAdminUserEntityId())) . '
            </span>';
        if ($event->getClassName() == Task::getClassName()) {
            $html .=
                '<span class="label label-info">
                    Giltig: ' . $event->getValidFromAsDateTimeString() . ' - ' . $event->getValidToAsDateTimeString() . '
                </span>
                <span class="label label-info">';
            if ($event->getIsRequested()) {
                $html .= 'Utförd: ' . $this->formatTimestamp($event->getTimeOfRequest());

            } else {
                $html .= 'Ej utförd';
            }
            $html .=
                '</span>';
        } else if ($event->getClassName() == Transaction::getClassName()) {
            $html .=
            ' <span class="label label-info">';
            if ($event->getHasResponse() && $event->getIsConfirmed()) {
                $html .=
                    'Överföringen gjordes: ' . $this->formatTimestamp($event->getTimeOfResponse());

            } else {
                $html .=
                    'Förfrågan gjordes: ' . $this->formatTimestamp($event->getTimeOfRequest());
            }
            $html .=
                '</span>';
        }
        $html .=
        '</p>
        <p class="list-group-item-text">
            <span class="label label-' . (($event->getIsConfirmed()) ? 'success' : (($event->getIsDenied()) ? 'danger' : (($event->getIsPending()) ? 'warning' : 'info'))) . ' pull-left">' .
        $event->getStatusText() . (($event->getIsConfirmed() || $event->getIsDenied()) ? ': ' . $event->getValue($this->model->isUserAdmin()) . ' ' . $this->model->getUnit()->getShortName() : '')
        . '</span>&nbsp;<span>' .
        $event->getDescription() . '</span>
        </p>';

        return $html;
    }

    protected function getHtmlForEventEdit() {
        $isEditTask = isset($_POST[self::$postEditTaskButtonNameKey]);
        $isNewTask = isset($_POST[self::$postNewTaskForAueIdButtonNameKey]);
        $isEditTransaction = isset($_POST[self::$postEditTransactionButtonNameKey]);
        $isNewTransaction = isset($_POST[self::$postNewTransactionForAueIdButtonNameKey]);

        if (!$isEditTask && !$isNewTask && !$isEditTransaction && !$isNewTransaction)
            return '';

        $isAdmin = $this->model->isUserAdmin();
        $taskId = 0;
        $transactionId = 0;
        $headingText = "";
        $unitMany = $this->model->getUnit()->getNameOfMany();

        if ($isEditTask) {
            $taskId = $_POST[self::$postEditTaskButtonNameKey];
            $event = $this->model->getTask($taskId);
            $aueId = $event->getAdminUserEntityId();
            $parentsName = $this->model->getParentsName($aueId);
            $childsName = $this->model->getChildsName($aueId);

            if ($event == null) {
                $this->model->setMessage("Fel vid hämtning av uppgift", MessageType::Error);
                return '';
            }
            $headingText = "Redigera befintlig uppgift";
        } else if ($isNewTask) {
            $aueId = $_POST[self::$postNewTaskForAueIdButtonNameKey];
            $parentsName = $this->model->getParentsName($aueId);
            $childsName = $this->model->getChildsName($aueId);
            $headingText = "Skapa ny uppgift för " . $childsName;
        } else if ($isEditTransaction) {
            $transactionId = $_POST[self::$postEditTransactionButtonNameKey];
            $event = $this->model->getTransaction($transactionId);
            $aueId = $event->getAdminUserEntityId();
            $parentsName = $this->model->getParentsName($aueId);
            $childsName = $this->model->getChildsName($aueId);

            if ($event == null) {
                $this->model->setMessage("Fel vid hämtning av uppgift", MessageType::Error);
                return '';
            }
            $headingText = "Redigera överföring";
        } else if ($isNewTransaction) {
            if ($isAdmin) {
                $headingText = "Skapa överföring";
            } else {
                $headingText = "Initiera överföring";
            }
            //$transactionId = $_POST[self::$postEditTransactionButtonNameKey];
            $aueId = $_POST[self::$postNewTransactionForAueIdButtonNameKey];
            $parentsName = $this->model->getParentsName($aueId);
            $childsName = $this->model->getChildsName($aueId);
        }

        $html = '
        <div class="panel panel-info">
            <div class="panel-heading">
                <div>
                    <h3 class="panel-title">' . $headingText . '
                    </h3>
                </div>
            </div>
            <div class="panel-body">
                <form action="' . $_SERVER['PHP_SELF'].'?'.$_SERVER["QUERY_STRING"] . '" method="post">';
                if ($isEditTask) {
                    $html .= '
                    <button type="submit"
                        class="btn btn-success pull-right"
                        name="' . self::$postUpdateTaskButtonNameKey . '"
                        value="' . $taskId . '">Uppdatera</button>';
                } else if ($isNewTask) {
                    $html .= '
                    <button type="submit"
                        class="btn btn-success pull-right"
                        name="' . self::$postExecuteNewTaskForAueIdButtonNameKey . '"
                        value="' . $aueId . '">Skapa</button>';

                } else if ($isEditTransaction) {
                    $html .= '
                    <button type="submit"
                        class="btn btn-success pull-right"
                        name="' . self::$postUpdateTransactionButtonNameKey . '"
                        value="' . $transactionId . '">Uppdatera</button>';
                } else if ($isNewTransaction) {
                    $html .= '
                    <button type="submit"
                        class="btn btn-success pull-right"
                        name="' . self::$postExecuteNewTransactionForAueIdButtonNameKey . '"
                        value="' . $aueId . '">Skapa</button>';
                }
                $html .= '
                    <a href="' . $_SERVER['PHP_SELF'].'?'.$_SERVER["QUERY_STRING"] . '">
                        <button type="button"
                            class="btn btn-warning pull-right">Avbryt
                        </button>
                    </a>';
                if ($isNewTask || $isEditTask) {
                    $html .= '
                        <div class="form-group col-lg-6">
                            <label for="titleId">Titel:</label>
                            <input class="form-control"
                                type="text"
                                name="' . self::$postEventTitleKey . '"
                                id="titleId"
                                placeholder="Ange titel här"
                                value="' . (($isEditTask) ? $event->getTitle() : '') . '"
                                autofocus />
                        </div>';
                }
                $html .= '
                    <div class="form-group col-lg-12">
                        <label for="descriptionId">Beskrivning:</label>
                        <input class="form-control"
                            type="text"
                            name="' . self::$postEventDescriptionKey . '"
                            placeholder="Ange beskrivning här"
                            value="' . (($isEditTask || $isEditTransaction) ? $event->getDescription() : '') . '"
                            id="descriptionId"
                            required/>
                    </div>';
                if ($isEditTransaction || $isNewTransaction) {
                    $html .= '
                    <div class="form-group col-lg-4">
                        <label for="transactionValueId">Överföring  (' . $unitMany . '):</label>
                        <input class="form-control"
                            type="number"
                            step=0.01
                            min=0.01
                            name="' . self::$postTransactionValueKey . '"
                            value="' . (($isEditTransaction) ? abs($event->getTransactionValue(false)) : 0) . '"
                            id="transactionValueId"
                            required/>
                    </div>';
                    $html .= '
                    <div class="form-group col-lg-4">';
                    if ($isNewTransaction) {
                        $isDeposit = true;
                    } else {
                        $isDeposit = ($event->getTransactionValue(false) > 0);
                    }
                    if ($isAdmin) {
                        $html .= '
                            <input type="radio" name="'. self::$postChangeSignOnTransactionKey . '" value="0" ' . ($isDeposit ? 'checked' : '') . '>Överföring till ' . $childsName . ' (Insättning)<br/>
                            <input type="radio" name="'. self::$postChangeSignOnTransactionKey . '" value="1" ' . ($isDeposit ? '' : 'checked') . '>Överföring från ' . $childsName . ' (Uttag)
                        ';
                    } else {
                        $html .= '
                            <input type="radio" name="'. self::$postChangeSignOnTransactionKey .'" value="1" ' . ($isDeposit ? '' : 'checked') . '>Överföring till ' . $parentsName . ' (Uttag)<br/>
                            <input type="radio" name="'. self::$postChangeSignOnTransactionKey .'" value="0" ' . ($isDeposit ? 'checked' : '') . '>Överföring från ' . $parentsName . ' (Insättning)
                        ';
                    }
                    $html .= '
                    </div>';
                } else if ($isEditTask || $isNewTask) {
                    $html .= '
                    <div class="form-group col-lg-4">
                        <label for="taskRewardValueId">Belöning (' . $unitMany . '):</label>
                        <input class="form-control"
                            type="number"
                            step=0.01
                            min=0
                            name="' . self::$postTaskRewardValueKey . '"
                            value="' . (($isEditTask) ? $event->getRewardValue(false) : 0) . '"
                            id="taskRewardValueId"
                            required/>
                    </div>';
                    $html .= '
                    <div class="form-group col-lg-4">
                        <label for="taskPenaltyValueId">Straff (' . $unitMany . '):</label>
                        <input class="form-control"
                            type="number"
                            step=0.01
                            max=0
                            name="' . self::$postTaskPenaltyValueKey . '"
                            value="' . (($isEditTask) ? $event->getPenaltyValue(false) : 0) . '"
                            id="taskPenaltyValueId"
                            required/>
                    </div>';
                }

                if ($isNewTask || $isEditTask) {
                    $html .= '
                        <div style="clear: both"></div>
                        <div class="form-group col-lg-3">
                            <label for="validFromId">Giltig från:</label>
                            <!--used for css, too-->
                            <input id="validFromId"
                                class="form-control"
                                value="' . (($isEditTask)? $event->getValidFromAsDateTimeString() : date('Y-m-d H:i:s', time())) .'"
                                name="' . self::$postTaskFromTimeKey . '"
                                required/>
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="validToId">Giltig till:</label>
                            <!--used for css, too-->
                            <input id="validToId"
                                class="form-control"
                                value="' . (($isEditTask)? $event->getValidToAsDateTimeString() : date('Y-m-d H:i:s', time())). '"
                                name="' . self::$postTaskToTimeKey . '"
                                required/>
                        </div>';
                }
                if ($isNewTask) {
                    $html .= '
                        <div class="form-group col-lg-1">
                            <label for="repeatWeeklyId">Repetera veckovis:</label>
                            <input id="repeatWeeklyId"
                                class="checkbox"
                                type="checkbox"
                                name="' . self::$postTaskRepeatWeeklyChecked . '" />
                        </div>
                        <div class="form-group col-lg-1">
                            <label for="repeatNumberOfTimesId">Antal gånger:</label>
                            <input id="repeatNumberOfTimesId"
                                class="form-control"
                                type="number"
                                min=1
                                name="' . self::$postTaskRepeatNumberOfTimes . '" />
                        </div>';
                }
                $html .= '
                </form>
            </div>
        </div>';

        return $html;
    }

    protected function getHtmlForCreateNewEvent() {
        $isAdmin = $this->model->isUserAdmin();
        if (TransactionsView::getClassName() == get_class($this)) {
            $buttonNameKey = self::$postNewTransactionForAueIdButtonNameKey;
        } else if (TasksView::getClassName() == get_class($this)) {
            $buttonNameKey = self::$postNewTaskForAueIdButtonNameKey;
        }
        $html = '';

        foreach ($this->model->getAdminUserEntities() as $aue) {
            if ($isAdmin) {
                $name = $aue->getUsersName();
                $id = $aue->getId();
            } else { //user cannot create a task, but well initiate a transaction
                $name = $aue->getAdminsName();
                $id = $aue->getId();
            }
            $html .= '
                <button type="submit"
                    class="btn btn-default"
                    name="' . $buttonNameKey . '"
                    value="' . $id . '">' . $name . '</button>';
        }
        return $html;
    }
}