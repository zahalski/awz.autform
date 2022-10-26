<?php

namespace Awz\AutForm\Interfaces;

interface SmsForm {

    /**
     * Html формы шаблона смс
     *
     * @param string $value
     * @return string
     */
    public static function tmplHtml(string $value);

    /**
     * Формирование массива параметров шаблона смс для сохранения
     *
     * @param array $arFields
     * @return array
     */
    public static function tmplHtmlSave(array $arFields);

    /**
     * Взаимодействие с смс модулем и отправка смс
     *
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Result or null
     */
    public static function OnAfterAddEvent(\Bitrix\Main\Event $event);

}