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

    public function getIsUpcoming() {
        //TODO: to be upcoming task cannot already be requested
        return $this->validTo > time();
    }


    public function getRewardValue() {
        return $this->rewardValue;
    }

    public function getPenaltyValue() {
        return $this->penaltyValue;
    }

    public function getValue($isAdmin) {
        $value = 0;

        if ($this->getIsConfirmed()) {
            $value = $this->rewardValue;
        } else if ($this->getIsDenied()) {
            $value = $this->penaltyValue;
        }

        if ($isAdmin) {
            $value = -$value;
        }
        return $value;
    }
}