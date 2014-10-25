<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\Model\Transaction;

class TransactionsView extends View {

    private $transactions;

    public function __construct(Model $model) {
        parent::__construct($model, "Transactions");

        $this->transactions = $this->model->getTransactions();

    }

    function getHtml() {
        $html =
$this->getHtmlForEventEdit() . '
<form action="' . $_SERVER['PHP_SELF'].'?'.$_SERVER["QUERY_STRING"] . '" method="post">
    <div class="well">
     Skapa ny överföring:' .
    $this->getHtmlForCreateNewTransaction() . '
    </div>
</form>
<div class="panel panel-info">
    <div class="panel-heading">
        <div>
            <h3 class="panel-title">Överföringar</h3>
        </div>
    </div>
    <div class="panel-body">
        <form action="' . $_SERVER['PHP_SELF'].'?'.$_SERVER["QUERY_STRING"] . '" method="post">
            <input type="hidden" name="' . $_POST[self::$postEventTypeKey] . '" value="' . Transaction::getClassName() . '" />
            <div class="list-group">' .
            $this->getHtmlForTransactionItems() . '
            </div>
        </form>
    </div>
</div>';
        return parent::getSurroundingHtml($html);
    }

    function getHtmlForTransactionItems() {
        $html = "";

        foreach($this->transactions as $transaction) {
            $html .= '
            <a href="#" class="list-group-item">
                <h4 class="list-group-item-heading">' .

                $this->getHtmlForEventButtonsOfItem($transaction) .

                $transaction->getTitle() .
                '<span class="label label-info">' .
                $transaction->getTransactionValue($this->model->isUserAdmin()) . ' ' . $this->model->getUnit()->getShortName()
                . '</span>' . '
                </h4>' .
                $this->getHtmlForEventLabelsOfItem($transaction) . '
            </a>
            ';
        }
        unset($transaction);

        return $html;
    }

    private function getHtmlForCreateNewTransaction() {
        $isAdmin = $this->model->isUserAdmin();
        $html = '';

        foreach ($this->model->getAdminUserEntities() as $aue) {
            if ($isAdmin) {
                $name = $aue->getUsersName();
                $id = $aue->getId();
            } else {
                $name = $aue->getAdminsName();
                $id = $aue->getId();
            }
            $html .= '
                <button type="submit"
                    class="btn btn-default"
                    name="' . self::$postNewTransactionForAueIdButtonNameKey . '"
                    value="' . $id . '">' . $name . '</button>';
        }

        return $html;
    }
}