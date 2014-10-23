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

    public function getTransactionValue($isAdmin) {
        $value = $this->transactionValue;
        if ($isAdmin) {
            $value = -$value;
        }
        return $value;
    }

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