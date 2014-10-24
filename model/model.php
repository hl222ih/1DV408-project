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

    private static $sessionActiveAdminUserEntityId = "Model::AAueId";

    private $user;
    private $adminUserEntities;
    private $unit;
    private $tasks;
    private $transactions;

    public function __construct() {
        $this->dao = new Dao();
        $this->user = new User($this->getLastPostedUsername(), 0, false, "", array());
        if ($this->isUserLoggedIn()) {
            if (!$this->isSessionIntegrityOk()) {
                $this->logoutUser();
            } else {
                $this->unit = new Unit("krona", "kronor", "kr");
                $this->user = $this->dao->getUserByUsername($this->getLastPostedUsername());
                $this->adminUserEntities = $this->dao->getAdminUserEntitiesByUserId($this->user->getId());
                $this->setActiveAdminUserEntityId(0); //visa alla anslutna aue:s som default

                //$this->units = $this->dao->getUnitsByUsersIds($this->user->getId(), $this->user->getMappedUsersIds()); //behöver 1) mappedUserIds -> all units for them.
                //TODO: borde bara hämta innehåll som är väsentligt för vyn
                $this->tasks = $this->dao->getTasksByUserId($this->user->getId());
                $this->transactions = $this->dao->getTransactionsByUserId($this->user->getId());
            }
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

    public function setMessage($message, $messageType = MessageType::Info) {
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

    public function getEvents() {
        $events = array();

        if ($this->user->getIsAdmin()) {
            $events = array_merge($this->tasks, $this->transactions);
        }

        usort($events, array($this, "compareEventsByTime"));

        return $events;
    }

    private function compareEventsByTime(Event $e1, Event $e2) {
        return $e1->getTimeOfRequest() - $e2->getTimeOfRequest();
    }

    public function getPendingEvents() {
        $events = $this->getEvents();

        $pendingEvents = array_filter($events, function($event) {
            return $event->getIsPending();
        });

        return $pendingEvents;
    }

    public function getTasks() {
        usort($this->tasks, array($this, "compareEventsByTime"));
        return $this->tasks;
    }

    public function getUpcomingTasks() {
        $upcomingTasks = array_filter($this->tasks, function($task) {
            return $task->getIsUpcoming();
        });

        usort($upcomingTasks, array($this, "compareEventsByTime"));

        return $upcomingTasks;
    }

    public function getTransactions() {
        usort($this->transactions, array($this, "compareEventsByTime"));

        return $this->transactions;
    }

    public function getParentsName($adminUserEntityId) {

        $aue = array_values(array_filter($this->adminUserEntities, function($aue) use(&$adminUserEntityId) {
            return $aue->getId() == $adminUserEntityId;
        }))[0];

        $parentsName = "name not found";
        if ($aue) {
            $parentsName = $aue->getAdminsName();
        }
        return $parentsName;
    }

    public function getChildsName($adminUserEntityId) {

        $aue = array_values(array_filter($this->adminUserEntities, function($aue) use( &$adminUserEntityId) {
            return $aue->getId() == $adminUserEntityId;
        }))[0];

        $childsName = "name not found";
        if ($aue) {
            $childsName = $aue->getUsersName();
        }
        return $childsName;
    }

    public function getTotalBalance() {
        $totalBalance = array_reduce($this->adminUserEntities, function($sum, $aue) {
            $sum += $aue->getBalance();
            return $sum;
        });

        if ($this->user->getIsAdmin()) {
            $totalBalance = -$totalBalance;
        }
        return $totalBalance . " " . $this->unit->getShortName();
    }

    public function getUnit() {
        return $this->unit;
    }

    /**
     * Used to check the genuineness of a request to make a change in tasks.
     * The one who makes the request might need to be admin and
     * the task needs to be in the current scope of tasks determined by the
     * user-admin-connection.
     *
     * @param $taskId - the id of the task
     * @param $needToBeAdmin - if user needs to be admin to be allowed to change the task.
     * @return bool
     */
    private function isAllowedToChangeTask($taskId, $needToBeAdmin) {
        $isAllowed = true;
        if ($needToBeAdmin) {
            if (!$this->isUserAdmin())
            {
                $this->setMessage("Du saknar rättigheter att göra ändringar i uppgifter.", MessageType::Error);
                $isAllowed = false;
            }
        }
        if (!array_filter($this->tasks, function ($task) use(&$taskId)  {
            return ($task->getId() == $taskId);
        })) {
            $isAllowed = false;
            $this->setMessage("Åtkomst nekad för aktuell uppgift.", MessageType::Error);
        };

        return $isAllowed;
    }

    public function confirmTaskDone($taskId) {
        if ($this->isAllowedToChangeTask($taskId, true)) {
            $this->dao->comfirmTaskDoneByTaskId($taskId);
        }
    }

    public function editTask($taskId) {
        //bör ju vara giltig taskId men inget krav på admin.
        //bör hämta taskinfo och förbereda redigering på sidan
        //med en submitbutton som uppdaterar databasen.
        if ($this->isAllowedToChangeTask($taskId, true)) {

            $this->dao->getTask($taskId);
        }
    }

    //TODO: form for creating or updating a Task.
    public function createTask() {
        if ($this->isUserAdmin()) {
            //$this->dao->createTask(...);
        }
    }
    public function updateTask() {
        if ($this->isAllowedToChangeTask($taskId, true)) {
            //$this->dao->updateTask($taskId,...);
        }
    }

    public function removeTask($taskId) {
    }

    public function regretMarkTaskDone($taskId) {
    }
    public function markTaskDone($taskId) {
    }
    public function confirmTransaction($taskId) {
    }
    public function editTransaction($taskId) {
    }
    public function regretTransaction($taskId) {
    }
    public function removeTransaction($taskId) {
    }

    public function getTask($taskId) {
        $task = array_values(array_filter($this->tasks, function ($task) use(&$taskId)  {
            return ($task->getId() == $taskId);
        }))[0];
        return $task;
    }
    public function getTransaction($transactionId) {
        $transaction = array_values(array_filter($this->transactions, function ($transaction) use(&$transactionId)  {
            return ($transaction->getId() == 2);
        }))[0];

        return $transaction;
    }

    public function getActiveAdminUserEntityId() {
        return isset($_SESSION[self::$sessionActiveAdminUserEntityId]) ? $_SESSION[self::$sessionActiveAdminUserEntityId] : false;
    }

    private function setActiveAdminUserEntityId($aueId) {
        $_SESSION[self::$sessionActiveAdminUserEntityId] = $aueId;
    }

    public function changeActiveAdminUserEntityId($aueId) {
        if ($aueId == 0) {
            $this->setMessage("Visar nu information för alla");
        } else {
            $aue = array_values(array_filter($this->adminUserEntities, function ($aue) use(&$aueId)  {
                return ($aue->getId() == $aueId);
            }))[0];

            if ($aue != null) {
                $this->setActiveAdminUserEntityId($aueId);
                if ($this->isUserAdmin()) {
                    $this->setMessage("Visar nu information för " . $this->getUsersName($aueId) . ".");
                } else {
                    $this->setMessage("Visar nu information för " . $this->getParentsName($aueId) . ".");
                }
            }
        }
    }
}