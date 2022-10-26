<?php

namespace Awz\AutForm;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Handlers {

    public static function onAfterEventsAdd(\Bitrix\Main\Event $event){
        $arParam = $event->getParameters();

        $arParam['EVENTS']['AWZ_'.mb_strtoupper(Events::SEND_SMS_CODE)] = array(
            "BX_EVENT" => array(
                array(
                    'awz.autform',
                    Events::SEND_SMS_CODE,
                    'awz.autform',
                    '\Awz\AutForm\HandleSms\Aut',
                    'OnAfterAddEvent',
                    'new'
                ),
            ),
            "FIELD" => array(
                "HTML" => array('\Awz\AutForm\HandleSms\Aut','tmplHtml'),
                "BEFORE_SAVE" => array('\Awz\AutForm\HandleSms\Aut','tmplHtmlSave')
            ),
            "NAME" => "AWZ: Форма авторизации. Смс код."
        );

        //echo'<pre>';print_r($arParam);echo'</pre>';
        //die();

        $result = new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            $arParam
        );
        return $result;
    }

    /**
     * Проверка и форматирование номера телефона
     *
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\EventResult
     */
    public static function checkPhone(\Bitrix\Main\Event $event){

        if(Option::get('awz.autform', 'CHECK_PHONE_MLIFE', 'N', '')!='Y'){
            return null;
        }

        $phone = $event->getParameter('phone');

        $result = new \Bitrix\Main\Result;

        if(!Loader::includeModule('mlife.smsservices')){
            $result->addError(new Error('Не установлен модуль mlife.smsservices'));
            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS,
                array('result'=>$result)
            );
        }

        $smsOb = new \Mlife\Smsservices\Sender();
        $check = $smsOb->checkPhoneNumber($phone);
        $phone = $check['phone'];
        if(!$check['check']){
            $result->addError(new Error('Неверный формат номера телефона'));
            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS,
                array('result'=>$result)
            );
        }
        $result->setData(array(
            'phone'=>$phone
        ));

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array('result'=>$result)
        );
    }

}