<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class GenericView extends ViewKeys {
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

    public function __construct(Model $model) {
        $this->model = $model;

        $this->username = (isset($_POST[self::$postUsernameKey]) ? $_POST[self::$postUsernameKey] : "");
        $this->password = (isset($_POST[self::$postPasswordKey]) ? $_POST[self::$postPasswordKey] : "");
        $this->autoLogin = (isset($_POST[self::$postAutoLoginCheckedKey]) ? true : false);
        $this->passwordAgain = (isset($_POST[self::$postPasswordAgainKey]) ? $_POST[self::$postPasswordAgainKey] : "");
        $this->name = (isset($_POST[self::$postNameKey]) ? $_POST[self::$postNameKey] : "");
        $this->createAdminAccount = (isset($_POST[self::$postAdminAccountCheckedKey]) ? true : false);
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

    //public function setEncryptedPasswordCookie($encryptedCookiePassword) {
    //    $_COOKIE[self::$cookieEncryptedPasswordKey] = $this->model->encryptCookiePassword($password);
    //}
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
        header('location: ' . $_SERVER['PHP_SELF'] . ($page ? "?". self::$getPageKey . "=" . $page : ""));
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

}