<?php

namespace BoostMyAllowanceApp\Model;


class Unit {
    private $nameOfOne;
    private $nameOfMany;
    private $shortName;

    public function __construct($nameOfOne, $nameOfMany, $shortName) {
        $this->nameOfOne = $nameOfOne;
        $this->nameOfMany = $nameOfMany;
        $this->shortName = $shortName;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNameOfMany()
    {
        return $this->nameOfMany;
    }

    public function getNameOfOne()
    {
        return $this->nameOfOne;
    }

    public function getShortName()
    {
        return $this->shortName;
    }
}