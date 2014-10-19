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
}