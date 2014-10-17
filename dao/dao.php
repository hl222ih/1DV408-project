<?php

namespace BoostMyAllowanceApp\Model\Dao;

use PDO;
use BoostMyAllowanceApp\Model\User;

require_once("database-config.php");

class Dao {

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
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return ($row ? true : false);
    }

    public function isUserAdmin($username) {
        $fieldIsAdmin = "is_admin";
        $isAdmin = false;

        $statement = $this->connection->prepare("
                          SELECT $fieldIsAdmin
                          FROM user
                          WHERE username = :username");
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row) {
           $isAdmin = ($row[$fieldIsAdmin]);
        }
        return $isAdmin;
    }

    public function doesCookiePasswordMatch($username, $encryptedCookiePassword) {
        $fieldCookiePassword = "cookie_password";

        $statement = $this->connection->prepare("
                          SELECT $fieldCookiePassword
                          FROM user
                          WHERE username = :username");
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
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
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
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
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $isMatch = false;
        if ($row) {
            if ($this->encryptPassword($password, $row[$fieldSalt]) == $row[$fieldSaltedPassword]) {
                $isMatch = true;
            }
        }

        return $isMatch;
    }

    private function encryptPassword($password, $salt) {
        $encryptedPassword = md5($salt.$password); //should probably use a better encryption algorithm
        return $encryptedPassword;
    }

    public function getUserByUsername($username) {
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
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(PDO::FETCH_ASSOC);

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

        $mappedUserIds = $statement->fetchAll(PDO::FETCH_COLUMN);

        $user = new User($username, $row[$fieldId], $row[$fieldIsAdmin], $row[$fieldName], $mappedUserIds);

        return $user;
    }


    public function registerNewUser($username, $password, $name, $isAdmin) {
        $tableUser = "user";
        $fieldUsername = "username";
        $fieldIsAdmin = "is_admin";
        $fieldName = "name";
        $fieldSecretToken = "secret_token";
        $fieldSaltedPassword = "salted_password";
        $fieldSalt = "salt";

        $secretToken = ""; //empty for now...
        $salt = $username; //username as salt
        $saltedPassword = $this->encryptPassword($password, $salt);

        $statement = $this->connection->prepare("
                        INSERT INTO $tableUser ($fieldIsAdmin, $fieldName, $fieldUsername, $fieldSecretToken, $fieldSaltedPassword, $fieldSalt)
                        VALUES (:is_admin, :name, :username, :secret_token, :salted_password, :salt)
                        ");
        $statement->bindParam(':is_admin', $isAdmin, PDO::PARAM_BOOL);
        $statement->bindParam(':name', $name, PDO::PARAM_STR);
        $statement->bindParam(':username', $username, PDO::PARAM_STR);
        $statement->bindParam(':secret_token', $secretToken, PDO::PARAM_STR);
        $statement->bindParam(':salted_password', $saltedPassword, PDO::PARAM_STR);
        $statement->bindParam(':salt', $salt, PDO::PARAM_STR);

        $isSuccess = $statement->execute();

        return $isSuccess;
    }

    //**A secret token would be needed to map another user to ones account
    //private function generateSecretToken() {
    //}

}
