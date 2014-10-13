<?php

namespace BoostMyAllowanceApp\Model;

use BoostMyAllowanceApp\Dal\Dal;

require_once("dal/dal.php");
require_once("admin-user-entity.php");
require_once("event.php");
require_once("log-item.php");
require_once("task.php");
require_once("transaction.php");
require_once("unit.php");
require_once("user.php");

class Model {

    const APP_NAME = "BoostMyAllowance!";
    private $pdo;

    private static $sessionIsLoggedInKey = "Model::IsLoggedIn";
    private static $sessionUsernameKey = "Model::Username";
    private static $sessionUserAgentKey = "Model::UserAgent";
    private static $sessionUserIPKey = "Model::UserIP";
    private static $sessionAutoLoginCheckedKey = "Model:AutoLogin";
    private static $sessionFeedbackMessageKey = "Model::FeedbackMessage";
    private static $sessionLastPostedUsername = "Model::LastPostedUsername";
    private static $sessionMappedUsers = "Model::MappedUsers";
    private static $sessionUnits = "Model::Units";
    private static $sessionUser = "Model::User";
    private static $sessionRequestedPage = "Model::RequestedPage";

    private $user;

    //private $adminUserEntities;

    public function __construct() {
        if ($this->isLoggedIn()) {
            if (!$this->isSessionIntegrityOk())
                $this->logout();
        }


        $this->pdo = new Dal();
        //$this->user = $this->dal->getUser($this->getUserName());
        //$this->otherUsers = $this->dal->getOtherUsers($this->user->getUserId());
        //$this->tasks = $this->dal->getTasks($this->user->getUserId, $this->otherUsers->getUserdId()); //en viss eller alla?

        //$this->adminUserEntities
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
    public function isLoggedIn() {
        return isset($_SESSION[self::$sessionIsLoggedInKey]) ? $_SESSION[self::$sessionIsLoggedInKey] : false;
    }

    private function setMessage($message) {
        $_SESSION[self::$sessionFeedbackMessageKey] = $message;
    }

    public function getMessage() {
        return isset($_SESSION[self::$sessionFeedbackMessageKey]) ? $_SESSION[self::$sessionFeedbackMessageKey] : "";
    }

    public function encryptCookiePassword($password) {
        $salt = $_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'];
        return md5($salt.$password);
    }
//    private function encryptPassword($password, $salt) {
//        return md5($salt.$password);
//    }

    public function cookieLogin($username, $encryptedCookiePassword) {
        $username = trim($username);
        $_SESSION[self::$sessionLastPostedUsername] = $username;
        $isSuccess = false;

        if ($this->pdo->doesUserExist($username)) {
            if ($this->pdo->doesCookiePasswordMatch($username, $encryptedCookiePassword)) {
                if ($this->pdo->isCookieExpirationValid($username)) {
                    $this->setMessage("Inloggningen lyckades via cookies");
                    $isSuccess = true;
                } else {
                    $this->setMessage("Felaktig information i cookie");
                }
            } else {
                $this->setMessage("Felaktig information i cookie");
            }
        }

        if ($isSuccess) {
            $_SESSION[self::$sessionIsLoggedInKey] = true;
            $_SESSION[self::$sessionUserAgentKey] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION[self::$sessionUsernameKey] = $username;
            $_SESSION[self::$sessionUserIPKey] = $_SERVER['REMOTE_ADDR'];
            $this->setUser(new User($username));
        } else {
            $this->logout();
        }
    }

    public function login($username, $password, $autoLogin) {
        $isSuccess = false;
        $username = trim($username);

        if (!$username) {
            $this->setMessage("Användarnamn saknas");
        } else {
            if (!$password) {
                $this->setMessage("Lösenord saknas");
            } else {
                if ($this->pdo->doesUserExist($username)) {
                    //TODO: well... change this
                    if ($this->pdo->doesPasswordMatch($username, $password)) {
                        if ($autoLogin) {
                            $this->setMessage("Inloggning lyckades och vi kommer ihåg dig nästa gång");
                        } else {
                            $this->setMessage("Inloggning lyckades");
                        }
                        $isSuccess = true;
                    } else {
                        $this->setMessage("Felaktigt användarnamn och/eller lösenord");
                    }
                } else {
                    $this->setMessage("Felaktigt användarnamn och/eller lösenord");
                }
            }
        }

        if ($isSuccess) {
            $_SESSION[self::$sessionIsLoggedInKey] = true;
            $_SESSION[self::$sessionAutoLoginCheckedKey] = $autoLogin;
            $_SESSION[self::$sessionUsernameKey] = $username;
            $_SESSION[self::$sessionUserAgentKey] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION[self::$sessionUserIPKey] = $_SERVER['REMOTE_ADDR'];
            $this->setUser(new User($username));
        } else {
            $this->logout();
        }
    }

    public function logout() {
        unset($_SESSION[self::$sessionIsLoggedInKey]);
        unset($_SESSION[self::$sessionUserAgentKey]);
        unset($_SESSION[self::$sessionUsernameKey]);
        unset($_SESSION[self::$sessionUserIPKey]);
        unset($_SESSION[self::$sessionUser]);
    }

    public function isAutoLoginChecked() {
        return isset($_SESSION[self::$sessionAutoLoginCheckedKey]) ? $_SESSION[self::$sessionAutoLoginCheckedKey] : false;
    }

    private function setUser(User $user) {
        $_SESSION[self::$sessionUser] = serialize($user);
    }

    public function getUser() {
        return unserialize($_SESSION[self::$sessionUser]);
    }

    public function isAdmin() {
        var_dump(unserialize($_SESSION[self::$sessionUser]));
        return $this->getUser()->isAdmin();
    }

    public function setRequestedPage($page) {
        $_SESSION[self::$sessionRequestedPage] = $page;
    }

    public function getRequestedPage() {
        return isset($_SESSION[self::$sessionRequestedPage]) ? $_SESSION[self::$sessionRequestedPage] : "";
    }
}