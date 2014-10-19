<?php

namespace BoostMyAllowanceApp\Model;


class Task extends Event {

    private $validFrom;
    private $validTo;

    private $rewardValue;
    private $penaltyValue;

    public function __construct($id, $adminUserEntityId, $unitId, $validFrom,
                                $validTo, $rewardValue, $penaltyValue, $timeOfRequest,
                                $timeOfResponse, $isConfirmed, $isDenied, $title, $description) {
        parent::__construct($id, $adminUserEntityId, $unitId, $timeOfRequest,
                            $timeOfResponse, $isConfirmed, $isDenied, $description, $title);
        $this->validFrom = $validFrom > 0 ? $validFrom : null;
        $this->validTo = $validTo > 0 ? $validTo : null;
        $this->rewardValue = $rewardValue;
        $this->penaltyValue = $penaltyValue;
    }

    public function getIsPending() {
        return !$this->isDenied &&
            !$this->isConfirmed &&
            $this->getIsRequested() &&
            $this->timeOfRequest < $this->validTo;
    }
}