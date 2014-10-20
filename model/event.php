<?php

namespace BoostMyAllowanceApp\Model;


class Event {

    protected $id;
    protected $adminUserEntityId;
    protected $unitId;

    protected $timeOfRequest;
    protected $timeOfResponse;

    protected $isConfirmed;
    protected $isDenied;

    protected $title;
    protected $description;


    public function __construct($id, $adminUserEntityId, $unitId, $timeOfRequest,
                                $timeOfResponse, $isConfirmed, $isDenied, $description, $title) {
        $this->id = $id;
        $this->adminUserEntityId = $adminUserEntityId;
        $this->unitId = $unitId;
        $this->timeOfRequest = $timeOfRequest > 0 ? $timeOfRequest : null;
        $this->timeOfResponse = $timeOfResponse > 0 ? $timeOfResponse : null;
        $this->isConfirmed = $isConfirmed;
        $this->isDenied = $isDenied;
        $this->title = $title;
        $this->description = $description;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getAdminUserEntityId() {
        return $this->adminUserEntityId;
    }

    public function getIsConfirmed() {
        return $this->isConfirmed;
    }

    public function getIsDenied() {
        return $this->isDenied;
    }

    public function getStatusText() {
        $statusText = "";
        if ($this->isConfirmed)
            $statusText = "Godkänd";
        else if ($this->isDenied)
            $statusText = "Nekad";
        else if ($this->getIsPending())
            $statusText = "Avvaktande";

        return $statusText;
    }

    public function getTimeOfRequest() {
        return $this->timeOfRequest;
    }

    public function getTimeOfResponse() {
        return $this->timeOfResponse;
    }

    public function getHasResponse() {
        return ($this->timeOfResponse != null);
    }

    public function getIsRequested() {
        return ($this->timeOfRequest != null);
    }

    public static function getClassName() {
        return get_called_class();
    }
}