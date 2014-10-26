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
    private $logItems;

    public function __construct() {
        try {
            $this->dao = new Dao();
            $this->user = new User($this->getLastPostedUsername(), 0, false, "", array());
            if ($this->isUserLoggedIn()) {
                if (!$this->isSessionIntegrityOk()) {
                    $this->logoutUser();
                } else {
                    $this->unit = new Unit("krona", "kronor", "kr");
                    $this->loadUser();
                    $this->loadAdminUserEntities();
                    $this->setActiveAdminUserEntityId(0); //visa alla anslutna aue:s som default
                    $this->loadTasks();
                    $this->loadTransactions();
                    $this->loadLogItems();
                }
            }
        } catch (\Exception $e) {
            throw new \Exception("Ooops! Tyvärr var det något som inte gick som det var tänkt. " . $e->getMessage(), MessageType::Error);
        }
    }

    public function loadUser() {
        $this->user = $this->dao->getUserByUsername($this->getLastPostedUsername());
    }
    public function loadAdminUserEntities() {
        $this->adminUserEntities = $this->dao->getAdminUserEntitiesByUserId($this->user->getId());
    }
    public function loadTasks() {
        $this->tasks = $this->dao->getTasksByUserId($this->user->getId());
    }
    public function loadTransactions() {
        $this->transactions = $this->dao->getTransactionsByUserId($this->user->getId());
    }
    public function loadLogItems() {
        $this->logItems = $this->dao->getLogItemsByUserId($this->user->getId());
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
                            $cookiePassword = $this->encryptCookiePassword($password);

                            //cookie expiration: 30 days (30*24*60*60)
                            $cookieExpirationTime = time() + 2592000;

                            if ($this->dao->storeCookieInfoByUsername($username, $cookieExpirationTime, $cookiePassword)) {
                                $this->setMessage("Inloggning lyckades och vi kommer ihåg dig nästa gång", MessageType::Success);
                            } else {
                                $this->setMessage("Inloggning lyckades", MessageType::Success); //men misslyckades med att lagra cookie-info i databasen.
                            }
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
    public function getAdminUserEntity($adminUserEntityId) {
        $aue = array_values(array_filter($this->adminUserEntities, function($aue) use(&$adminUserEntityId) {
            return $aue->getId() == $adminUserEntityId;
        }))[0];

        return $aue;
    }
    public function getParentsName($adminUserEntityId) {

        $aue = $this->getAdminUserEntity($adminUserEntityId);

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
     * user-admin-connection (adminUserEntities).
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

    /**
     * Used to check the genuineness of a request to make a change in transactions.
     * The one who makes the request might need to be admin and
     * the transaction needs to be in the current scope of transactions determined by the
     * user-admin-connections (adminUserEntities).
     *
     * @param $transactionId - the id of the transaction
     * @param $needToBeAdmin - if user needs to be admin to be allowed to change the transaction.
     * @return bool
     */
    private function isAllowedToChangeTransaction($transactionId, $needToBeAdmin) {
        $isAllowed = true;
        if ($needToBeAdmin) {
            if (!$this->isUserAdmin())
            {
                $this->setMessage("Du saknar rättigheter att göra ändringar i överföringar.", MessageType::Error);
                $isAllowed = false;
            }
        }
        if (!array_filter($this->transactions, function ($transaction) use(&$transactionId)  {
            return ($transaction->getId() == $transactionId);
        })) {
            $isAllowed = false;
            $this->setMessage("Åtkomst nekad för aktuell överföring.", MessageType::Error);
        };

        return $isAllowed;
    }

    public function confirmTaskDone($taskId) {
        if ($this->isAllowedToChangeTask($taskId, true)) {
            $task = $this->getTask($taskId);
            $aueId = $task->getAdminUserEntityId();
            $rewardValue = $task->getRewardValue(false);
            $unitMany = $this->unit->getNameOfMany();
            $aue = $this->getAdminUserEntity($aueId);
            $currentBalance = $aue->getBalance();
            if ($this->dao->confirmTaskDoneByTaskId($taskId, $aueId, $currentBalance, $rewardValue, $unitMany)) {
                $this->setMessage("Uppgiften har godkänts och saldot har uppdaterats.", MessageType::Success);
                $this->loadAdminUserEntities();
                $this->loadTasks();
            } else {
                $this->setMessage("Uppgiften kunde inte godkännas.", MessageType::Error);
            }
        }
    }

    public function updateTask($taskId, $title, $description, $rewardValue, $penaltyValue, $validFrom, $validTo) {
        $title = ($title) ? $title : "Uppgift";
        $description = ($description) ? $description : "Uppgiften saknar beskrivning.";
        if ($this->isAllowedToChangeTask($taskId, true)) {
            if ($rewardValue < 0) {
                $this->setMessage("Belöningsvärdet från inte vara under 0.", MessageType::Error);
            } else if ($penaltyValue > 0) {
                $this->setMessage("Straffvärdet får inte vara över 0.", MessageType::Error);
            } else if (strtotime($validFrom) < time()) {
                $this->setMessage("Uppgifter får inte vara giltiga från och med bakåt i tiden.", MessageType::Error);
            } else if (strtotime($validFrom) >= strtotime($validTo)) {
                $this->setMessage("Uppgifter måste ha en positiv giltighetstid.", MessageType::Error);
            } else {
                if ($this->dao->updateTask($taskId, $title, $description, $rewardValue, $penaltyValue,
                    strtotime($validFrom), strtotime($validTo))) {
                    $this->setMessage("Uppgiften har uppdaterats.", MessageType::Success);
                } else {
                    $this->setMessage("Uppgiften kunde inte skapas.", MessageType::Error);
                }
            }
        } else {
            $this->setMessage("Du saknar rättighet att uppdatera uppgiften.", MessageType::Error);
        }
    }

    public function updateTransaction($transactionId, $description, $transactionValue, $shouldChangeSign) {
        if ($transactionValue == 0) {
            $this->setMessage("En överföring får inte ha värdet 0.", MessageType::Error);
        } else if ($this->isAllowedToChangeTransaction($transactionId, false)) {
            $transaction = $this->getTransaction($transactionId);
            if ($transaction->getIsPending()) {
                if ($shouldChangeSign) {
                    $transactionValue = -$transactionValue;
                }
                if ($this->dao->updateTransaction($transactionId, $description, $transactionValue)) {
                    $this->setMessage("Överföringen har uppdaterats.", MessageType::Success);
                    $this->loadTransactions();
                } else {
                    $this->setMessage("Överföringen kunde inte uppdateras.", MessageType::Error);
                }
            } else {
                $this->setMessage("Ändringar kan bara göras i en överföring som är väntande", MessageType::Error);
            }
        } else {
            $this->setMessage("Rättigheter saknas att göra ändringar i överföringen.", MessageType::Error);
        }

    }

    public function regretTransaction($transactionId) {
        if ($this->isAllowedToChangeTransaction($transactionId, false)) {
            $transaction = $this->getTransaction($transactionId);
            if ($transaction->getIsPending()) {
                $this->dao->removeTransaction($transactionId); //regret a pending transaction == remove the transaction
            } else {
                $this->setMessage("Endast en överföring som är väntande kan ångras.", MessageType::Error);
            }
        } else {
            $this->setMessage("Rättigheter saknas att göra ångra överföringen.", MessageType::Error);
        }
    }

    public function removeTask($taskId) {
        if ($this->isAllowedToChangeTask($taskId, true)) {
            if ($this->dao->removeTask($taskId)) {
                $this->setMessage("Uppgiften har tagits bort", MessageType::Success);
                $this->loadTasks();
            } else {
                $this->setMessage("Uppgiften kunde inte tas bort.", MessageType::Error);
            }
        } else {
            $this->setMessage("Rättigheter saknas för att ta bort uppgiften.", MessageType::Error);
        }
    }

    public function regretMarkTaskDone($taskId) {
        if ($this->isAllowedToChangeTask($taskId, false)) {
            if ($this->dao->markTaskUndone($taskId)) {
                $this->setMessage("Uppgiften har markerats som ej utförd.", MessageType::Success);
                $this->loadTasks();
            } else {
                $this->setMessage("Uppgiften kunde inte markeras som ej utförd.", MessageType::Error);
            }
        } else {
            $this->setMessage("Rättigheter saknas att markera uppgiften som ej utförd.", MessageType::Error);
        }
    }

    public function markTaskDone($taskId) {
        if ($this->isAllowedToChangeTask($taskId, false)) {
            if ($this->dao->markTaskDone($taskId)) {
                $this->setMessage("Uppgiften har markerats som utförd.", MessageType::Success);
                $this->loadTasks();
            } else {
                $this->setMessage("Uppgiften kunde inte markeras som utförd.", MessageType::Error);
            }
        }
    }

    public function confirmTransaction($transactionId) {
        if ($this->isAllowedToChangeTransaction($transactionId, true)) {
            $transaction = $this->getTransaction($transactionId);
            $aueId = $transaction->getAdminUserEntityId();
            $transactionValue = $transaction->getTransactionValue(false);
            $unitMany = $this->unit->getNameOfMany();
            $aue = $this->getAdminUserEntity($aueId);
            $currentBalance = $aue->getBalance();

            if ($this->dao->confirmTransaction($transactionId, $aueId, $currentBalance, $transactionValue, $unitMany)) {
                $this->setMessage("Överföringen har godkänts och saldot har uppdaterats.", MessageType::Success);
                $this->loadAdminUserEntities();
                $this->loadTransactions();
            } else {
                $this->setMessage("Överföringen kunde inte godkännas.", MessageType::Error);
            }
        }
    }

    public function denyTransaction($transactionId) {
        if ($this->isAllowedToChangeTransaction($transactionId, true)) {
            $transaction = $this->getTransaction($transactionId);
            if ($transaction->getIsDenied()) {
                $this->setMessage("Överföringen är redan nekad", MessageType::Error);
            } else if ($this->dao->denyTransaction($transactionId)) {
                $this->setMessage("Överföringen har markerats nekad", MessageType::Success);
            } else {
                $this->setMessage("Överföringen kunde inte markeras nekad", MessageType::Error);
            }
        }
    }

    public function removeTransaction($transactionId) {
        if ($this->isAllowedToChangeTransaction($transactionId, true)) {
            if ($this->dao->removeTransaction($transactionId)) {
                $this->setMessage("Överföringen har tagits bort", MessageType::Success);
                $this->loadTasks();
            } else {
                $this->setMessage("Överföringen kunde inte tas bort.", MessageType::Error);
            }
        } else {
            $this->setMessage("Rättigheter saknas för att ta bort överföringen.", MessageType::Error);
        }
    }

    public function getTask($taskId) {
        $task = array_values(array_filter($this->tasks, function ($task) use(&$taskId)  {
            return ($task->getId() == $taskId);
        }))[0];

        return $task;
    }

    public function getTransaction($transactionId) {
        $transaction = array_values(array_filter($this->transactions, function ($transaction) use(&$transactionId)  {
            return ($transaction->getId() == $transactionId);
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

    public function getSecretToken() {
        return $this->dao->getSecretTokenByUserId($this->user->getId());
    }

    public function connectAccounts($username, $secretToken) {
        if ($this->dao->connectAccounts($this->user->getId(), $this->isUserAdmin(), $username, $secretToken)) {
            $this->setMessage("Kontona har nu kopplats ihop.", MessageType::Success);
            $this->loadAdminUserEntities();
        } else {
            $this->setMessage("Ihopkopplingen av kontona misslyckades.", MessageType::Error);
        }
    }

    public function getAdminUserEntities() {
        return $this->adminUserEntities;
    }

    public function createNewTransaction($adminUserIdentityId, $description, $value, $shouldChangeSign) {
        if ($value == 0) {
            $this->setMessage("En överföring får inte ha värdet 0.", MessageType::Error);
        } else if (!$this->doesAueIdBelongToUser($adminUserIdentityId)) {
            $this->setMessage("Du saknar rättigheter att hantera överföringen", MessageType::Error);
        } else {
            if ($shouldChangeSign) {
                $value = -$value;
            }
            if ($this->isUserAdmin()) {
                $isConfirmed = true;
            } else {
                $isConfirmed = false;
            }
            if ($this->dao->insertNewTransaction($adminUserIdentityId, $description, $value, $isConfirmed)) {
                if ($isConfirmed) {
                    $this->setMessage("Överföringen är utförd", MessageType::Success);
                } else {
                    $this->setMessage("Överföringen har initierats och väntar på godkännande", MessageType::Success);
                }
                $this->loadTransactions();
            } else {
                $this->setMessage("Överföringen kunde inte skapas", MessageType::Error);
            }
        }
    }

    /**
     * If adminUserEntityId is posted from server, this function can be used
     * to verify that the currently logged in user is part of corresponding adminUserIdentity
     * @param $adminUserEntityId
     * @return bool
     */
    private function doesAueIdBelongToUser($adminUserEntityId) {
        if ($this->user->getIsAdmin()) {
            $adminId = $this->user->getId();
            $aue = array_values(array_filter($this->adminUserEntities, function ($aue) use(&$adminUserEntityId, &$adminId)  {
                return ($aue->getId() == $adminUserEntityId && $aue->getAdminsId() == $adminId);
            }))[0];
        } else {
            $userId = $this->user->getId();
            $aue = array_values(array_filter($this->adminUserEntities, function ($aue) use(&$adminUserEntityId, &$userId)  {
                return ($aue->getId() == $adminUserEntityId && $aue->getUsersId() == $userId);
            }))[0];
        }
        $isBelonging = ($aue != null);
        return $isBelonging;
    }

    public function createNewTask($adminUserEntityId, $title, $description, $rewardValue, $penaltyValue,
                                  $validFrom, $validTo, $repeatNumberOfWeeks) {
        $title = ($title) ? $title : "Uppgift";
        $description = ($description) ? $description : "Uppgiften saknar beskrivning.";
        $repeatNumberOfWeeks = ($repeatNumberOfWeeks >= 1) ? $repeatNumberOfWeeks : 1;
        if (!$this->doesAueIdBelongToUser($adminUserEntityId) || !$this->isUserAdmin()) {
            $this->setMessage("Du saknar rättigheter att skapa uppgiften.", MessageType::Error);
        } else if ($rewardValue < 0) {
            $this->setMessage("Belöningsvärdet från inte vara under 0.", MessageType::Error);
        } else if ($penaltyValue > 0) {
            $this->setMessage("Straffvärdet får inte vara över 0.", MessageType::Error);
        } else if (strtotime($validFrom) < time()) {
            $this->setMessage("Uppgifter får inte vara giltiga från och med bakåt i tiden.", MessageType::Error);
        } else if (strtotime($validFrom) >= strtotime($validTo)) {
            $this->setMessage("Uppgifter måste ha en positiv giltighetstid.", MessageType::Error);
        } else {
            $numberOfSuccessfulRecordsCreated = $this->dao->insertNewTask($adminUserEntityId, $title, $description, $rewardValue, $penaltyValue,
                strtotime($validFrom), strtotime($validTo), $repeatNumberOfWeeks);
            if ($numberOfSuccessfulRecordsCreated) {
                $this->setMessage( $numberOfSuccessfulRecordsCreated . " uppgifter har skapats.", MessageType::Success);
                $this->loadTasks();
            } else {
                if ($repeatNumberOfWeeks > 1) {
                    $this->setMessage("Uppgifterna kunde inte skapas.", MessageType::Error);
                } else {
                    $this->setMessage("Uppgiften kunde inte skapas.", MessageType::Error);
                }
            }
        }
    }

    public function getLogItems() {
        return $this->logItems;
    }
}