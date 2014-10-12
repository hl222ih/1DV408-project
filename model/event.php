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

    protected $description;
} 