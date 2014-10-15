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
            $this->connection = new PDO(
                'mysql:host=' . $this->config->host . ';dbname=' . $this->config->database,
                $this->config->username,
                $this->config->password
            );
        } catch (\PDOException $e) {
            //TODO: error handling...
        }
    }
    public function doesUserExist($username) {
        $statement = $this->connection->prepare('
                        SELECT 1
                        FROM user
                        WHERE username = :username');
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return ($row ? true : false);
    }

    public function doesCookiePasswordMatch($username, $encryptedCookiePassword) {
        $fieldCookiePassword = "cookie_password";

        $statement = $this->connection->prepare("
                          SELECT $fieldCookiePassword
                          FROM user
                          WHERE username = :username");
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $match = false;
        if ($row) {
            $dbCookiePassword = isset($row[$fieldCookiePassword]) ? $row[$fieldCookiePassword] : false;
            if ($dbCookiePassword) {
                if ($dbCookiePassword == $encryptedCookiePassword) {
                    $match = true;
                }
            }
        }
        return $match;
    }

    public function isCookieExpirationValid($username) {
        $fieldCookieExpiration = "cookie_expiration";

        $statement = $this->connection->prepare("
                          SELECT *
                          FROM user
                          WHERE username = :username");
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $isValid = false;
        if ($row) {
            $dbCookieExpiration = isset($row[$fieldCookieExpiration]) ? $row[$fieldCookieExpiration] : false; //isset really needed?
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
        $fieldSalt = "salt";
        $fieldSaltedPassword = "salted_password";
        $tableUser = "user";
        $fieldUsername = "username";

        $statement = $this->connection->prepare("
                          SELECT $fieldSalt, $fieldSaltedPassword
                          FROM $tableUser
                          WHERE $fieldUsername = :username");
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $isMatch = false;
        if ($row) {
            $dbSalt = isset($row[$fieldSalt]) ? $row[$fieldSalt] : false; //isset maybe not needed
            $dbSaltedPassword = isset($row[$fieldSaltedPassword]) ? $row[$fieldSaltedPassword] : false; //isset maybe not needed
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

    /**
     * @param $username
     * @return array with id, isAdmin, name and mappedUserIds (zero-indexed array)
     */
    public function getUserInfo($username) {
        $tableUser = "user";
        $tableUserToUser = "user_to_user";
        $fieldId = "id";
        $fieldUsername = "username";
        $fieldIsAdmin = "is_admin";
        $fieldName = "name";
        $fieldParentUserId = "parent_user_id";
        $fieldChildUserId = "child_user_id";
        //$fieldSecretToken = "secret_token"; might be needed later

        $statement = $this->connection->prepare("
                        SELECT $fieldId, $fieldIsAdmin, $fieldName
                        FROM $tableUser
                        WHERE $fieldUsername = :username");
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $info = array(
            "id" => $row[$fieldId],
            "isAdmin" => $row[$fieldIsAdmin],
            "name" => $row[$fieldName]
        );
        if ($row[$fieldIsAdmin]) {
            $statement = $this->connection->prepare("
                            SELECT $fieldChildUserId
                            FROM $tableUserToUser
                            WHERE $fieldParentUserId = $row[$fieldId]");
        } else {
            $statement = $this->connection->prepare("
                            SELECT $fieldParentUserId
                            FROM $tableUserToUser
                            WHERE $fieldChildUserId = $row[$fieldId]");
        }
        $statement->execute();

        $col = $statement->fetchAll(PDO::FETCH_COLUMN);

        $info['mappedUserIds'] = $col;
        return $info;
    }
}
