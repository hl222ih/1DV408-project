<?php

namespace BoostMyAllowanceApp\Model;

use BoostMyAllowanceApp\Model\Dao\Dao;

require_once("dao/dao.php");
require_once("admin-user-entity.php");
require_once("event.php");
require_once("log-item.php");
require_once("task.php");
require_once("transaction.php");
require_once("unit.php");
require_once("user.php");
require_once("message-type.php");
require_once("message.php");

class Model {

    const APP_NAME = "BoostMyAllowance!";
    private $dao;

    private static $sessionUserAgentKey = "Model::UserAgent";
    private static $sessionUserIPKey = "Model::UserIP";

    private static $sessionAutoLoginCheckedKey = "Model:AutoLogin";
    private static $sessionFeedbackMessageKey = "Model::FeedbackMessage";
    private static $sessionLastPostedUsername = "Model::LastPostedUsername";
    private static $sessionRequestedPage = "Model::RequestedPage";
    private static $sessionLastPostedName = "Model::LastPostedName";
    private static $sessionLastRegisterAdminAccountChecked = "Model:LastPostedRegisterAdminAccountChecked";

    private $user;

    public function __construct() {
        $this->dao = new Dao();
        $this->user = new User($this->getLastPostedUsername(), 0, false, "", array());
        if ($this->isUserLoggedIn()) {
            if (!$this->isSessionIntegrityOk())
                $this->logoutUser();
            else
                $this->user = $this->dao->getUserByUsername($this->getLastPostedUsername());
        }
    }

    private function doesUserAgentMatch() {
        $match = false;
        if (isset($_SESSION[self::$sessionUserAgentKey]))
            $match = ($_SESSION[self::$sessionUserAgentKey] == $_SERVER['HTTP_USER_AGENT']);
        return $match;
    }

    private function doesIPMatch() {
        $match = false;
        if (isset($_SESSION[self::$sessionUserIPKey]))
            $match = ($_SESSION[self::$sessionUserIPKey] == $_SERVER['REMOTE_ADDR']);
        return $match;
    }

    public function isSessionIntegrityOk() {
        return $this->doesUserAgentMatch() && $this->doesIPMatch();
    }

    /**
     * @return bool
     */
    public function isUserLoggedIn() {
        return $this->user->isLoggedIn();
    }

    private function setMessage($message, $messageType = MessageType::Info) {
        //$_SESSION[self::$sessionFeedbackMessageKey] = $message;
        $_SESSION[self::$sessionFeedbackMessageKey] = serialize(new Message($message, $messageType));
    }

    public function getMessage() {
        return isset($_SESSION[self::$sessionFeedbackMessageKey]) ? unserialize($_SESSION[self::$sessionFeedbackMessageKey]) : "";
    }

    public function hasMessage() {
        return isset($_SESSION[self::$sessionFeedbackMessageKey]);
    }

    public function unsetMessage() {
        unset($_SESSION[self::$sessionFeedbackMessageKey]);
    }

    public function encryptCookiePassword($password) {
        $salt = $_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'];
        return md5($salt.$password);
    }

    public function cookieLogin($username, $encryptedCookiePassword) {
        $username = trim($username);
        $this->setLastPostedUsername($username);
        $isSuccess = false;

        if ($this->dao->doesUserExist($username)) {
            if ($this->dao->doesCookiePasswordMatch($username, $encryptedCookiePassword)) {
                if ($this->dao->isCookieExpirationValid($username)) {
                    $this->setMessage("Inloggningen lyckades via cookies", MessageType::Success);
                    $isSuccess = true;
                } else {
                    $this->setMessage("Felaktig information i cookie", MessageType::Error);
                }
            } else {
                $this->setMessage("Felaktig information i cookie", MessageType::Error);
            }
        }

        if ($isSuccess) {
            $this->user->setLoggedIn(true);
            $this->user->setIsAdmin($this->dao->isUserAdmin($username));
            $_SESSION[self::$sessionUserAgentKey] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION[self::$sessionUserIPKey] = $_SERVER['REMOTE_ADDR'];
        } else {
            $this->logoutUser();
        }
    }

    public function login($username, $password, $autoLogin) {
        $isSuccess = false;
        $username = trim($username);
        $this->setLastPostedUsername($username);

        if (!$username) {
            $this->setMessage("Användarnamn saknas", MessageType::Error);
        } else {
            if (!$password) {
                $this->setMessage("Lösenord saknas", MessageType::Error);
            } else {
                if ($this->dao->doesUserExist($username)) {
                    if ($this->dao->doesPasswordMatch($username, $password)) {
                        if ($autoLogin) {
                            $this->setMessage("Inloggning lyckades och vi kommer ihåg dig nästa gång", MessageType::Success);
                        } else {
                            $this->setMessage("Inloggning lyckades", MessageType::Success);
                        }
                        $isSuccess = true;
                    } else {
                        $this->setMessage("Felaktigt användarnamn och/eller lösenord", MessageType::Error);
                    }
                } else {
                    $this->setMessage("Felaktigt användarnamn och/eller lösenord", MessageType::Error);
                }
            }
        }

        if ($isSuccess) {
            $this->user->setLoggedIn(true);
            $this->user->setIsAdmin($this->dao->isUserAdmin($username));
            $_SESSION[self::$sessionAutoLoginCheckedKey] = $autoLogin;
            $_SESSION[self::$sessionUserAgentKey] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION[self::$sessionUserIPKey] = $_SERVER['REMOTE_ADDR'];
        } else {
            $this->logoutUser();
        }
    }

    public function logoutUser() {
        $this->user->setLoggedIn(false);

        unset($_SESSION[self::$sessionUserAgentKey]);
        unset($_SESSION[self::$sessionUserIPKey]);
    }

    public function isAutoLoginChecked() {
        return isset($_SESSION[self::$sessionAutoLoginCheckedKey]) ? $_SESSION[self::$sessionAutoLoginCheckedKey] : false;
    }

    //private function setUser() {
    //    $this->dao->getUserByUsername($this->getUsersUsername());
    //    $_SESSION[self::$sessionUser] = serialize($user);
    //}

    //public function getUser() {
        //return unserialize($_SESSION[self::$sessionUser]);

    //}

    public function isUserAdmin() {
        return $this->user->getIsAdmin();
    }

    public function setRequestedPage($page) {
        $_SESSION[self::$sessionRequestedPage] = $page;
    }

    public function getRequestedPage() {
        return isset($_SESSION[self::$sessionRequestedPage]) ? $_SESSION[self::$sessionRequestedPage] : "";
    }

    public function getLastPostedUsername() {
        return isset($_SESSION[self::$sessionLastPostedUsername]) ? $_SESSION[self::$sessionLastPostedUsername] : "";
    }

    public function getLastPostedName() {
        return isset($_SESSION[self::$sessionLastPostedName]) ? $_SESSION[self::$sessionLastPostedName] : "";
    }

    public function getUsersUsername() {
        return $this->user->getUsername();
    }

    public function getUsersName() {
        return $this->user->getName();
    }

    public function setLastPostedUsername($username) {
        $username = trim($username);

        if ($username) {
            $_SESSION[self::$sessionLastPostedUsername] = $username;
        }
    }
    public function setLastPostedName($name) {
        $name = trim($name);

        if ($name) {
            $_SESSION[self::$sessionLastPostedName] = $name;
        }
    }

    public function unsetLastPostedName() {
        unset($_SESSION[self::$sessionLastPostedName]);
    }

    public function setLastPostedRegisterAdminAccountChecked($wasAdminChecked) {
        if ($wasAdminChecked) {
            $_SESSION[self::$sessionLastRegisterAdminAccountChecked] = true;
        } else {
            unset($_SESSION[self::$sessionLastRegisterAdminAccountChecked]);
        }
    }

    public function getLastPostedRegisterAdminAccountChecked() {
        return isset($_SESSION[self::$sessionLastRegisterAdminAccountChecked]) ? true : false;
    }

    public function registerNewUser($username, $password, $passwordAgain, $name, $createAdminAccount) {
        //TODO: verify user data
        $isInputOk = true;
        $isSuccess = false;

        //for starters...
        if ($password != $passwordAgain) {
            $isInputOk = false;
            $this->setMessage("Lösenorden matchar inte.", MessageType::Error);
        }

        if ($isInputOk) {
            $isSuccess = $this->dao->registerNewUser($username, $password, $name, $createAdminAccount);
            if ($isSuccess) {
                $this->unsetLastPostedName();
                $this->setMessage("Användarkonto för " . $name . " har skapats.", MessageType::Success);
                $this->setLastPostedUsername($username);
            } else {
                $this->setMessage("Ett oväntat fel inträffade när användarkontot skulle skapas.", MessageType::Error);
                $this->setLastPostedUsername($username);
                $this->setLastPostedRegisterAdminAccountChecked(true);
            }
        } else {
            $this->setLastPostedName($name);
            $this->setLastPostedRegisterAdminAccountChecked(true);
            $this->setLastPostedUsername($username);
        }

        return $isSuccess;
    }
}