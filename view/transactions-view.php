<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class TransactionsView extends View {

    private $transaction;

    public function __construct(Model $model) {
        parent::__construct($model, "Transactions");

        $this->transactions = $this->model->getTransactions();

    }

    function getHtml() {
        $html = '
<div class="panel panel-info">
    <div class="panel-heading">
        <div>
            <h3 class="panel-title">Överföringar</h3>
        </div>
    </div>
    <div class="panel-body">
        <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
            <div class="list-group">' .
            $this->getHtmlForTransactionLines() . '
            </div>
        </form>
    </div>
</div>';
        return parent::getSurroundingHtml($html);
    }

    function getHtmlForTransactionLines() {
        $html = "";

 /*       protected static $postConfirmTransactionButtonNameKey = "View::ConfirmTransaction";
        protected static $postEditTransactionButtonNameKey = "View::EditTransaction";
        protected static $postRegretTransactionButtonNameKey = "View::RegretTransaction";
        protected static $postRemoveTransactionButtonNameKey = "View::RemoveTransaction";*/


        foreach($this->transactions as $transaction) {
            $html .= '
            <a href="#" class="list-group-item">
                <h4 class="list-group-item-heading">' .
                (($this->model->isUserAdmin()) ? '
                    <input type="submit"
                        class="btn btn-danger pull-right"
                        name="' . self::$postConfirmTransactionButtonNameKey . '"
                        value="Godkänn" />' : '') .
                (($transaction->getIsPending()) ? '
                        <input type="submit"
                            class="btn btn-info pull-right"
                            name="' . self::$postEditTransactionButtonNameKey . '"
                            value="Redigera" />' : '') .
                (($transaction->getIsPending() && !$this->model->isUserAdmin()) ? '
                        <input type="submit"
                            class="btn btn-success pull-right"
                            name="' . self::$postRegretTransactionButtonNameKey . '"
                            value="Ångra" />' : '') .
                (($transaction->getIsPending() && $this->model->isUserAdmin()) ? '
                        <input type="submit"
                            class="btn btn-success pull-right"
                            name="' . self::$postRemoveTransactionButtonNameKey . '"
                            value="Radera" />' : '') .
                $transaction->getTitle() .
                '<span class="label label-info">' .
                $transaction->getTransactionValue() . ' ' . $this->model->getUnit()->getShortName()
                . '</span>' .
                '</h4>.
                <p>
                    <span class="label label-info">' .
                    $this->model->getChildsName($transaction->getAdminUserEntityId())
                    . '</span>' . '
                    <span class="label label-info">' .
                        (($transaction->getIsRequested()) ?
                            'Utförd: ' . $this->formatTimestamp($transaction->getTimeOfRequest())
                            : 'Ej utförd') . '
                    </span>' . '
                    <span class="label label-info">' .
                        (($transaction->getHasResponse() && $transaction->getIsConfirmed()) ?
                            'Överföringen gjordes: ' . $this->formatTimestamp($transaction->getTimeOfResponse())
                            : 'Förfrågan gjordes: ' . $this->formatTimestamp($transaction->getTimeOfRequest())) . '
                    </span>' . '
                </p>
                <p class="list-group-item-text">
                    <span class="label label-' . (($transaction->getIsConfirmed()) ? 'success' : (($transaction->getIsDenied()) ? 'danger' : (($transaction->getIsPending()) ? 'warning' : 'info'))) . ' pull-left">' .
                $transaction->getStatusText() . (($transaction->getIsConfirmed() || $transaction->getIsDenied()) ? ': ' . $transaction->getValue($this->model->isUserAdmin()) . ' ' . $this->model->getUnit()->getShortName() : '')
                . '</span>&nbsp;<span>
                    ' . $transaction->getDescription() . '</span>
                </p>
            </a>
            ';
        }
        unset($transaction);

        return $html;
    }
} 