<?php

namespace BoostMyAllowanceApp\Model;


abstract class Event {

    protected $id;
    protected $adminUserEntityId;
    protected $unitId;

    protected $timeOfRequest; //a request will be made when the child has done the task and wants the task reward or transaction amount
    protected $timeOfResponse;

    //The parent will confirm the event (task or transaction) and release the award or
    //transaction amount from his or her account to the child's account
    protected $isConfirmed;
    //The event can be denied which may give the child a penalty for a task,
    //while denial of a transaction will just make it void.
    //Unfortunately at the moment, the application lacks some logic to automatically
    //deny a task when the time runs out.
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

    public function getId() {
        return $this->id;
    }

    //different implementation in Task and Transaction respectively, therefore abstract here.
    abstract function getValue($isAdmin);

    //different implementation in Task and Transaction respectively, therefore abstract here.
    abstract function getIsPending();
}