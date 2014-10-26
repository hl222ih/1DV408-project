<?php

namespace BoostMyAllowanceApp\Model;


class Transaction extends Event {
    private $transactionValue;


    public function __construct($id, $adminUserEntityId, $unitId, $timeOfRequest,
                                $timeOfResponse, $isConfirmed, $isDenied, $description, $transactionValue) {
        parent::__construct($id, $adminUserEntityId, $unitId, $timeOfRequest,
            $timeOfResponse, $isConfirmed, $isDenied, $description, "Överföring");
        $this->transactionValue = $transactionValue;
    }

    public function getIsPending() {
        return !$this->isDenied && !$this->isConfirmed && $this->timeOfRequest;
    }

    /**
     * The transaction may be positive for the parent at the same time as negative for the child and vice versa.
     * Returns the size of the transaction with sign depending on if the user is admin or not.
     *
     * @param $isAdmin
     * @return mixed
     */
    public function getTransactionValue($isAdmin) {
        $value = $this->transactionValue;
        if ($isAdmin) {
            $value = -$value;
        }
        return $value;
    }

    /**
     * The transaction may be positive for the parent at the same time as negative for the child and vice versa.
     *
     * Takes into consideration if the transaction is confirmed or not. If not, the actual value will be 0.
     * @param $isAdmin
     * @return int
     */
    public function getValue($isAdmin) {
        $value = 0;

        if ($this->getIsConfirmed()) {
            $value = $this->transactionValue;
        }

        if ($isAdmin) {
            $value = -$value;
        }
        return $value;
    }
}