<?php

namespace Awz\AutForm;

class Events {

    /**
     * Отправка смс кода
     */
    const SEND_SMS_CODE = 'onSendSmsCode';

    /**
     * Проверка и форматирование номера телефона
     */
    const CHECK_PHONE = 'checkPhone';

    /**
     * Создание массива номеров для поиска
     */
    const AFTER_CREATE_PHONES = 'onAfterCreatePhones';

    /**
     * Переопределение поиска ид юзера
     */
    const FIND_USER = 'onFindUser';

    /**
     * Переопределение поиска ид юзера по логину
     */
    const FIND_USER_FROM_LOGIN = 'onFindUserLogin';

    /**
     * Своя проверка лимитов
     */
    const CHECK_LIMITS = 'onCheckLimits';

    /**
     * Своя проверка кода
     */
    const CHECK_CODE = 'onCheckCode';

    /**
     * После проверки лимитов
     */
    const AFTER_CHECK_LIMITS = 'onAfterCheckLimits';

    /**
     * После входа через смс
     */
    const AFTER_AUTH_SMS = 'onAfterAuthSms';

    /**
     * После входа по паролю
     */
    const AFTER_AUTH_PSW = 'onAfterAuthPsw';

}