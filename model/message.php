<?php

namespace BoostMyAllowanceApp\Model;


class Message {
    private $message;
    private $messageType;

    public function __construct($message, $messageType) {
        $this->message = $message;
        $this->messageType = $messageType;
    }

    public function getMessageText() {
        return $this->message;
    }

    public function getMessageType() {
        return $this->messageType;
    }

} 