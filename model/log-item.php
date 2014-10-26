<?php

namespace BoostMyAllowanceApp\Model;

/**
 * Class LogItem
 * Quite straight forward. Log messages for balance changing events happening.
 * Could be much more user friendly than what is implemented at the moment.
 * @package BoostMyAllowanceApp\Model
 */
class LogItem {

    private $id;
    private $adminUserEntityId;
    private $message;
    private $timeOfLog;

    public function __construct($id, $adminUserEntityId, $message, $timeOfLog) {
        $this->id = $id;
        $this->adminUserEntityId = $adminUserEntityId;
        $this->message = $message;
        $this->timeOfLog = $timeOfLog;
    }

    public function getId() {
        return $this->id;
    }

    public function getAdminUserEntityId() {
        return $this->adminUserEntityId;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getTimeOfLog() {
        return $this->timeOfLog;
    }

} 