<?php

namespace BoostMyAllowanceApp\Model;

abstract class MessageType {
    const Info = 0;
    const Success = 1;
    const Error = 2;
    const Warning = 3;
}