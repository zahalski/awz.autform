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

}