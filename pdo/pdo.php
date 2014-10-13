<?php

namespace BoostMyAllowanceApp\PDO;


class PDO {


    public function doesUserExist($username) {
        //TODO: checkup in DB
        return true; //temp
    }

    public function doesCookiePasswordMatch($username, $encryptedCookiePassword) {
        //TODO: checkup in DB
        return true; //temp
    }

    public function isCookieExpirationValid($username) {
        //TODO: checkup in DB
        return true; //temp
    }

    public function doesPasswordMatch($username, $password) {
        //TODO: get salt for $username stored in db to encrypt $password and compare with db-encrypted password
        return true; //temp
    }
}