<?php

namespace BoostMyAllowanceApp\View;

use BoostMyAllowanceApp\Model\MessageType;
use BoostMyAllowanceApp\Model\Model;
use BoostMyAllowanceApp\Model\Task;
use BoostMyAllowanceApp\Model\Transaction;
use BoostMyAllowanceApp\Model\Event;

require_once("partials/head.php");
require_once("partials/footer.php");
require_once("partials/navigation.php");

abstract class View extends ViewKeys {

    private $title;
    protected $model;

    public function __construct(Model $model, $viewTitle = "") {
        $this->model = $model;
        $this->title = $model::APP_NAME . " " . $viewTitle;
    }

    protected function getSurroundingHtml($bodyHtml) {
        $html = $this->getFirstPartOfHtml();
        $html .= $bodyHtml;
        $html .= $this->getSecondPartOfHtml();

        return $html;
    }

    private function getFirstPartOfHtml() {
        $head = new Head($this->title);
        $navigation = new Navigation($this->model, get_class($this));

        $html = '<!DOCTYPE html>' . PHP_EOL;
        $html .= $head->getHtml();
        $html .= '<body>' . PHP_EOL;
        $html .= $navigation->getHtml();
        $html .= ($this->model->hasMessage() ? '<div class="alert alert-' .
            $this->getAlertCssClass($this->model->getMessage()->getMessageType()) .
            '" role="alert"><a href="#" class="close" data-dismiss="alert">&times;</a>
        ' . $this->model->getMessage()->getMessageText() . '</div>' : '');

        return $html;
    }

    private function getSecondPartOfHtml() {
        $footer = new Footer();

        $html = $footer->getHtml();
        $html .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>';
        $html .= '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>';
        $html .= '</body>' . PHP_EOL;

        return $html;
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

    public function setEncryptedPasswordToCookie($encryptedCookiePassword) {
        $_COOKIE[self::$cookieEncryptedPasswordKey] = $encryptedCookiePassword;
    }
    public function setCookiesIfAutoLogin() {
        if ($this->autoLogin) {
            $encryptedCookiePassword = $this->model->encryptCookiePassword($_POST[$this->postPasswordKey]);
            setcookie($this->cookieUsernameKey, $_POST[$this->postUsernameKey], time()+2592000, '/'); //expire in 30 days
            setcookie($this->cookieEncryptedPasswordKey, $encryptedCookiePassword, time()+2592000, '/');
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

    public static function getClassName() {
        return get_called_class();
    }

    public static function getPageName() {
        preg_match("/\\\\(\w*)View$/", get_called_class(), $matches);
        return lcfirst($matches[1]);
    }

    public function getAlertCssClass($messageType) {

        switch ($messageType) {
            case MessageType::Info:
                $alertCssClass = "info";
                break;
            case MessageType::Success:
                $alertCssClass = "success";
                break;
            case MessageType::Error:
                $alertCssClass = "danger";
                break;
            case MessageType::Warning:
                $alertCssClass = "warning";
                break;
            default:
                $alertCssClass = "default";
        }

        return $alertCssClass;
    }

    protected function formatTimestamp($timestamp) {
        return date("Y-m-d H:i", $timestamp);
    }

    protected function getHtmlForEventButtonsOfItems(Event $event) {
        $html = '';

        if ($event->getClassName() == Task::getClassName()) {

            $html =
                (($this->model->isUserAdmin()) ? '
                <input type="submit"
                    class="btn btn-danger pull-right"
                    name="' . self::$postRemoveTaskButtonNameKey . '"
                    value="Radera" />' : '') .
                (($this->model->isUserAdmin()) ? '
                <input type="submit"
                    class="btn btn-info pull-right"
                    name="' . self::$postEditTaskButtonNameKey . '"
                    value="Redigera" />' : '') .
                (($event->getIsPending() && $this->model->isUserAdmin()) ? '
                <input type="submit"
                    class="btn btn-success pull-right"
                    name="' . self::$postConfirmTaskDoneButtonNameKey . '"
                    value="Godkänn" />' : '') .
                ((!$event->getIsRequested()) ? '
                <input type="submit"
                    class="btn btn-success pull-right"
                    name="' . self::$postMarkTaskDoneButtonNameKey . '"
                    value="Markera som utförd" />' : '') .
                (($event->getIsPending()) ? '
                <input type="submit"
                    class="btn btn-danger pull-right"
                    name="' . self::$postRegretMarkTaskDoneButtonNameKey . '"
                    value="Markera som ej utförd" />' : '');
        } else if ($event->getClassName() == Transaction::getClassName()) {
            $html =
                (($this->model->isUserAdmin()) ? '
                <input type="submit"
                class="btn btn-danger pull-right"
                name="' . self::$postConfirmTransactionButtonNameKey . '"
                value="Godkänn" />' : '') .
                (($event->getIsPending()) ? '
                <input type="submit"
                    class="btn btn-info pull-right"
                    name="' . self::$postEditTransactionButtonNameKey . '"
                    value="Redigera" />' : '') .
                (($event->getIsPending() && !$this->model->isUserAdmin()) ? '
                <input type="submit"
                    class="btn btn-success pull-right"
                    name="' . self::$postRegretTransactionButtonNameKey . '"
                    value="Ångra" />' : '') .
                (($event->getIsPending() && $this->model->isUserAdmin()) ? '
                <input type="submit"
                    class="btn btn-success pull-right"
                    name="' . self::$postRemoveTransactionButtonNameKey . '"
                    value="Radera" />' : '');
        }
        return $html;
    }
}