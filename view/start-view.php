<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

/**
 * Class StartView
 * This is a special view class that is used to handle input from made POST:s (and GET:s), no matter
 * which page the POST:s (or GET:s) are made from.
 * The provided interface with methods helps the controller to decide which "real" view to load and display aswell
 * as call functions in the model with information provided by the GET:s and POST:s.
 *
 * @package BoostMyAllowanceApp\View
 */
class StartView extends ViewKeys {
    static private $cookieEncryptedPasswordKey = "View::EncryptedPassword";
    static private $cookieUsernameKey = "View::Username";
    static private $getPageKey = "page";

    private $model;

    //form fields...
    private $username;
    private $password;
    private $autoLogin;
    private $passwordAgain;
    private $name;
    private $createAdminAccount;
    private $aueId;
    private $connectAccountName;
    private $connectAccountToken;

    public function __construct(Model $model) {
        $this->model = $model;

        $this->username = (isset($_POST[self::$postUsernameKey]) ? $_POST[self::$postUsernameKey] : "");
        $this->password = (isset($_POST[self::$postPasswordKey]) ? $_POST[self::$postPasswordKey] : "");
        $this->autoLogin = (isset($_POST[self::$postAutoLoginCheckedKey]) ? true : false);
        $this->passwordAgain = (isset($_POST[self::$postPasswordAgainKey]) ? $_POST[self::$postPasswordAgainKey] : "");
        $this->name = (isset($_POST[self::$postNameKey]) ? $_POST[self::$postNameKey] : "");
        $this->createAdminAccount = (isset($_POST[self::$postAdminAccountCheckedKey]) ? true : false);
        $this->aueId = (isset($_POST[self::$postChangeAdminUserEntityIdKey]) ? $_POST[self::$postChangeAdminUserEntityIdKey] : 0);
        $this->connectAccountName = (isset($_POST[self::$postConnectAccountUsernameKey]) ? $_POST[self::$postConnectAccountUsernameKey] : "");
        $this->connectAccountToken = (isset($_POST[self::$postConnectAccountTokenKey]) ? $_POST[self::$postConnectAccountTokenKey] : "");
        $this->aueId = isset($_POST[self::$postExecuteNewTransactionForAueIdButtonNameKey]) ? $_POST[self::$postExecuteNewTransactionForAueIdButtonNameKey] : $this->aueId;
        $this->aueId = isset($_POST[self::$postExecuteNewTaskForAueIdButtonNameKey]) ? $_POST[self::$postExecuteNewTaskForAueIdButtonNameKey] : $this->aueId;
    }

    public function unsetCookies() {
        unset($_COOKIE[self::$cookieEncryptedPasswordKey]);
        setcookie(self::$cookieEncryptedPasswordKey, null, -1, '/');
        unset($_COOKIE[self::$cookieUsernameKey]);
        setcookie(self::$cookieUsernameKey, null, -1, '/');
    }

    public function getUsernameFromCookie() {
        return (isset($_COOKIE[self::$cookieUsernameKey]) ? $_COOKIE[self::$cookieUsernameKey] : "");
    }

    public function getEncryptedPasswordFromCookie() {
        return (isset($_COOKIE[self::$cookieEncryptedPasswordKey]) ? $_COOKIE[self::$cookieEncryptedPasswordKey] : "");
    }

    public function setCookiesIfAutoLogin() {
        if ($this->autoLogin) {
            $encryptedCookiePassword = $this->model->encryptCookiePassword($_POST[self::$postPasswordKey]);
            setcookie(self::$cookieUsernameKey, $_POST[self::$postUsernameKey], time()+2592000, '/'); //expire in 30 days
            setcookie(self::$cookieEncryptedPasswordKey, $encryptedCookiePassword, time()+2592000, '/');
        }
    }
    public function wasLoginButtonClicked() {
        return isset($_POST[self::$postLoginButtonNameKey]);
    }
    public function wasRegisterButtonClicked() {
        return isset($_POST[self::$postRegisterButtonNameKey]);
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function wasAutoLoginChecked() {
        return $this->autoLogin;
    }

    public function getRequestedPage() {
        return isset($_GET[self::$getPageKey]) ? $_GET[self::$getPageKey] : "";
    }

    public function redirectPage($page = "") {
        header('location: ' . $_SERVER['PHP_SELF'] . ($page ? "?". self::$getPageKey . "=" . $page : '?'.$_SERVER["QUERY_STRING"]));
        die;
    }

    public function wasCreateAdminAccountChecked() {
        return $this->createAdminAccount;
    }

    public function getName() {
        return $this->name;
    }

    public function getPasswordAgain() {
        return $this->passwordAgain;
    }

    public function getEventId() {
        $postEventButtonNameKeys = [
            self::$postConfirmTaskDoneButtonNameKey,
            self::$postEditTaskButtonNameKey,
            self::$postRemoveTaskButtonNameKey,
            self::$postRegretMarkTaskDoneButtonNameKey,
            self::$postMarkTaskDoneButtonNameKey,
            self::$postConfirmTransactionButtonNameKey,
            self::$postEditTransactionButtonNameKey,
            self::$postRegretTransactionButtonNameKey,
            self::$postRemoveTransactionButtonNameKey,
            self::$postUpdateTaskButtonNameKey,
            self::$postUpdateTransactionButtonNameKey
        ];

        $eventId = false;
        $count = 0;
        $numberOfKeys = array_count_values($postEventButtonNameKeys);
        while (!$eventId && $count < $numberOfKeys) {
            $eventId = (isset($_POST[$postEventButtonNameKeys[$count]])) ? $_POST[$postEventButtonNameKeys[$count]] : false;
            $count++;
        }

        return $eventId;
    }

    public function wasConfirmTaskDoneButtonClicked() {
        return isset($_POST[self::$postConfirmTaskDoneButtonNameKey]);
    }

    public function wasUpdateTaskButtonClicked() {
        return isset($_POST[self::$postUpdateTaskButtonNameKey]);
    }

    public function wasUpdateTransactionButtonClicked() {
        return isset($_POST[self::$postUpdateTransactionButtonNameKey]);
    }

    public function wasEditTaskButtonClicked() {
        return isset($_POST[self::$postEditTaskButtonNameKey]);
    }

    public function wasRemoveTaskButtonClicked() {
        return isset($_POST[self::$postRemoveTaskButtonNameKey]);
    }

    public function wasRegretMarkTaskDoneButtonClicked() {
        return isset($_POST[self::$postRegretMarkTaskDoneButtonNameKey]);
    }

    public function wasMarkTaskDoneButtonClicked() {
        return isset($_POST[self::$postMarkTaskDoneButtonNameKey]);
    }

    public function wasConfirmTransactionButtonClicked() {
        return isset($_POST[self::$postConfirmTransactionButtonNameKey]);
    }

    public function wasEditTransactionButtonClicked() {
        return isset($_POST[self::$postEditTransactionButtonNameKey]);
    }

    public function wasRegretTransactionButtonClicked() {
        return isset($_POST[self::$postRegretTransactionButtonNameKey]);
    }

    public function wasRemoveTransactionButtonClicked() {
        return isset($_POST[self::$postRemoveTransactionButtonNameKey]);
    }

    public function wasChangeAdminUserEntityButtonClicked() {
        return isset($_POST[self::$postChangeAdminUserEntityIdKey]);
    }

    public function getAdminUserEntityId() {
        return $this->aueId;
    }

    public function wasConnectAccountsButtonClicked() {
        return isset($_POST[self::$postConnectAccountsButtonNameKey]);
    }

    public function getConnectAccountName() {
        return $this->connectAccountName;
    }

    public function getConnectAccountToken() {
        return $this->connectAccountToken;
    }

    public function wasNewTransactionForAueIdButtonClicked() {
        return isset($_POST[self::$postNewTransactionForAueIdButtonNameKey]);
    }

    public function wasExecuteNewTransactionForAueIdButtonClicked() {
        return isset($_POST[self::$postExecuteNewTransactionForAueIdButtonNameKey]);
    }

    public function getDescription() {
        return isset($_POST[self::$postEventDescriptionKey]) ? $_POST[self::$postEventDescriptionKey] : "";
    }

    public function getTransactionValue() {
        return isset($_POST[self::$postTransactionValueKey]) ? $_POST[self::$postTransactionValueKey] : 0;
    }

    public function getChangeValue() {
        return isset($_POST[self::$postChangeSignOnTransactionKey]) ? $_POST[self::$postChangeSignOnTransactionKey] : false;
    }

    public function wasExecuteNewTaskForAueIdButtonClicked() {
        return isset($_POST[self::$postExecuteNewTaskForAueIdButtonNameKey]);
    }

    public function getTitle() {
        return isset($_POST[self::$postEventTitleKey]) ? $_POST[self::$postEventTitleKey] : "";
    }

    public function getRewardValue() {
        return isset($_POST[self::$postTaskRewardValueKey]) ? $_POST[self::$postTaskRewardValueKey] : 0;
    }

    public function getPenaltyValue() {
        return isset($_POST[self::$postTaskPenaltyValueKey]) ? $_POST[self::$postTaskPenaltyValueKey] : 0;
    }

    public function getValidFrom() {
        return isset($_POST[self::$postTaskFromTimeKey]) ? $_POST[self::$postTaskFromTimeKey] : 0;
    }

    public function getValidTo() {
        return isset($_POST[self::$postTaskToTimeKey]) ? $_POST[self::$postTaskToTimeKey] : 0;
    }

    public function getRepeatNumberOfWeeks() {
        $numberOfWeeks = 1;
        $isRepeatChecked = isset($_POST[self::$postTaskRepeatWeeklyChecked]) ? $_POST[self::$postTaskRepeatWeeklyChecked] : false;
        if ($isRepeatChecked) {
            $numberOfWeeks = isset($_POST[self::$postTaskRepeatNumberOfTimes]) ? $_POST[self::$postTaskRepeatNumberOfTimes] : 1;
            $numberOfWeeks = ($numberOfWeeks >= 1) ? $numberOfWeeks : 1;
        }
        return $numberOfWeeks;
    }
}








