<?php

namespace BoostMyAllowanceApp\View;

/**
 * Class ViewKeys
 *
 * Many variables used in different $_POST buttons on different pages.
 * This class should be inherited by all other view classes.
 *
 * @package BoostMyAllowanceApp\View
 */
class ViewKeys {
    protected static $postLoginButtonNameKey = 'View::LoginButtonName';
    protected static $postRegisterButtonNameKey = 'View::RegisterButtonName';
    protected static $postUsernameKey = "View::Username";
    protected static $postNameKey = "View::Name";
    protected static $postPasswordKey = "View::Password";
    protected static $postAutoLoginCheckedKey = "View::AutoLoginChecked";
    protected static $postPasswordAgainKey = "View::PasswordAgain";
    protected static $postAdminAccountCheckedKey = "View::AdminAccountChecked";

    protected static $postEventIdKey = "View::EventId";
    protected static $postEventTypeKey = "View::EventType";

    protected static $postConfirmTaskDoneButtonNameKey = "View::ConfirmTaskDone";
    protected static $postEditTaskButtonNameKey = "View::EditTask";
    protected static $postRemoveTaskButtonNameKey = "View::RemoveTask";
    protected static $postRegretMarkTaskDoneButtonNameKey = "View::RegretMarkTaskDone";
    protected static $postMarkTaskDoneButtonNameKey = "View::MarkTaskDone";

    protected static $postUpdateTaskButtonNameKey = "View::UpdateTask";
    protected static $postConfirmTransactionButtonNameKey = "View::ConfirmTransaction";
    protected static $postEditTransactionButtonNameKey = "View::EditTransaction";
    protected static $postRegretTransactionButtonNameKey = "View::RegretTransaction";
    protected static $postRemoveTransactionButtonNameKey = "View::RemoveTransaction";

    protected static $postUpdateTransactionButtonNameKey = "View::UpdateTransaction";
    protected static $postNewTransactionForAueIdButtonNameKey = "View::NewTransactionForAueId";
    protected static $postNewTaskForAueIdButtonNameKey = "View::NewTaskForAueId";

    protected static $postEventDescriptionKey = "View::EventDescription";
    protected static $postEventTitleKey = "View::EventTitle";
    protected static $postTaskRewardValueKey = "View::TaskRewardValue";
    protected static $postTaskPenaltyValueKey = "View::TaskPenaltyValue";
    protected static $postTransactionValueKey = "View::TransactionValue";

    protected static $postChangeSignOnTransactionKey = "View::ChangeSignForTransaction";
    protected static $postChangeAdminUserEntityIdKey = "View::ChangeAueId";

    protected static $postConnectAccountUsernameKey = "View::ConnectAccountUsername";
    protected static $postConnectAccountTokenKey = "View::ConnectAccountToken";
    protected static $postConnectAccountsButtonNameKey = "View::ConnectAccounts";

    protected static $postExecuteNewTransactionForAueIdButtonNameKey = "View::ExecuteNewTransactionForAueId";
    protected static $postExecuteNewTaskForAueIdButtonNameKey = "View::ExecuteNewTaskForAueId";

    protected static $postTaskFromTimeKey = "View::TaskFromTime";
    protected static $postTaskToTimeKey = "View::TaskToTime";
    protected static $postTaskRepeatWeeklyChecked = "View::TaskRepeatWeeklyChecked";
    protected static $postTaskRepeatNumberOfTimes = "View::TaskRepeatNumberOfTimes";
}