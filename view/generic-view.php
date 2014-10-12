<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\Model;

class GenericView {
    static private $cookieEncryptedPasswordKey = "View::EncryptedPasswordKey";
    static private $cookieUsernameKey = "View::UsernameKey";
    static private $postUsernameKey = "View::Username";
    static private $postPasswordKey = "View::Password";
    static private $postAutoLoginCheckedKey = "View::AutoLoginChecked";
    static private $postLoginButtonNameKey = "View::LoginButtonNameKey";

    private $model;

    public function __construct(Model $model) {
        $this->model = $model;

        $this->username = (isset($_POST[self::$postUsernameKey]) ? $_POST[self::$postUsernameKey] : "");
        $this->password = (isset($_POST[self::$postPasswordKey]) ? $_POST[self::$postPasswordKey] : "");
        $this->autoLogin = (isset($_POST[self::$postAutoLoginCheckedKey]) ? true : false);
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

    public function getUsername() {
        return $this->username;
    }
    public function getPassword() {
        return $this->password;
    }
    public function wasAutoLoginChecked() {
        return $this->autoLogin;
    }

} 