<?php

namespace BoostMyAllowanceApp\Model;


class Task extends Event {

    private $validFrom;
    private $validTo;

    //the reward given if the user performs a task within the given time frame.
    private $rewardValue;
    //the (optional) penalty if the user does not perform a task within the given time frame.
    //logic to update the balance automatically is unfortunately not implemented at the moment.
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
        //might need a slight adjustment on what is the most practical interpretation of being "upcoming".
        return $this->validTo > time();
    }


    /**
     * Reward value is a positive amount for the child, while it is a negative amount for the parent.
     * @param $isAdmin
     * @return mixed
     */
    public function getRewardValue($isAdmin) {
        $value = $this->rewardValue;
        if ($isAdmin) {
            $value = -$value;
        }
        return $value;
    }

    /**
     * Penalty value is a negative amount for the child, while it is a positive amount for the parent.
     * @param $isAdmin
     * @return mixed
     */
    public function getPenaltyValue($isAdmin) {
        $value = $this->penaltyValue;
        if ($isAdmin) {
            $value = -$value;
        }
        return $value;
    }

    /**
     * This function returns the value of a task based on if it is confirmed or denied or neither
     * as well as if the perspective of positive/negative is the one of the parent(admin) or the child(user).
     *
     * @param $isAdmin
     * @return int
     */
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

    public function getValidFrom() {
        return $this->validFrom;
    }

    public function getValidFromAsDateTimeString() {
        return date('Y-m-d H:i:s', $this->validFrom);
    }

    public function getValidTo() {
        return $this->validTo;
    }

    public function getValidToAsDateTimeString() {
        return date('Y-m-d H:i:s', $this->validTo);
    }
}