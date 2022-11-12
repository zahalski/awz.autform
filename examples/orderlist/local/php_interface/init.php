<?php
/*
 * записываем подтвержденный физически номер телефона пользователя
 * в специальное свойство USER с кодом "UF_CHECK_PHONE" (тип строка)
 * */
\Bitrix\Main\EventManager::getInstance()->addEventHandler(
    'awz.autform',
    'onAfterAuthSms',
    array('handlersAwz','onAfterAuthSms')
);


class handlersAwz {

    /**
     * вызывается после авторизации и регистрации+вход через компонент awz:autform
     *
     * @param \Bitrix\Main\Event $event
     */
    public static function onAfterAuthSms(\Bitrix\Main\Event $event){

        $userId = $event->getParameter('user');
        $phone = $event->getParameter('phone');

        if($userId){
            $oUser = new CUser;
            $oUser->Update($userId,array('UF_CHECK_PHONE'=>$phone));
        }

    }

}