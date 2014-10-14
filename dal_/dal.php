<?php

namespace BoostMyAllowanceApp\Dal;

use PDO;
require_once("database-config.php");

class Dal {

    private $connection;
    private $config;

    public function __construct() {
        $this->config = new DatabaseConfig();

        try {
            $this->connection = new PDO('mysql:host='.$this->config->host.";dbname=".$this->config->database,
                $this->config->username, $this->config->password);
        } catch (\PDOException $e) {
            //echo $e;
            //die;
            //TODO: error handling...
        }
    }
    public function doesUserExist($username) {
        $statement = $this->connection->prepare('SELECT * FROM user WHERE username = :username');
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return ($row ? true : false);
    }

    public function doesCookiePasswordMatch($username, $encryptedCookiePassword) {
        $statement = $this->connection->prepare('SELECT * FROM user WHERE username = :username');
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $match = false;
        if ($row) {
            $dbCookiePassword = isset($row['cookie_password']) ? $row['cookie_password'] : false;
            if ($dbCookiePassword) {
                if ($dbCookiePassword == $encryptedCookiePassword) {
                    $match = true;
                }
            }
        }
        return $match;
    }

    public function isCookieExpirationValid($username) {
        $statement = $this->connection->prepare('SELECT * FROM user WHERE username = :username');
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $isValid = false;
        if ($row) {
            $dbCookieExpiration = isset($row['cookie_expiration']) ? $row['cookie_expiration'] : false;
            if ($dbCookieExpiration) {
                if ($dbCookieExpiration > time()) {
                    $isValid = true;
                }
            }
        }

        return $isValid;
    }

    public function doesPasswordMatch($username, $password) {
        //get users salt, get users salted_password
        $statement = $this->connection->prepare('SELECT * FROM user WHERE username = :username');
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $isMatch = false;
        if ($row) {
            $dbSalt = isset($row['salt']) ? $row['salt'] : false;
            $dbSaltedPassword = isset($row['salted_password']) ? $row['salted_password'] : false;
            if ($dbSalt && $dbSaltedPassword) {
                if ($this->encryptPassword($password, $dbSalt) == $dbSaltedPassword) {
                    $isMatch = true;
                }
            }
        }

        return $isMatch;
    }

    private function encryptPassword($password, $salt) {
        $encryptedPassword = md5($salt.$password); //should probably use a better encryption algorithm
        return $encryptedPassword;
    }
}
