<?php

namespace Awz\AutForm;

use Bitrix\Main\Error;
use Bitrix\Main\EventResult;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Result;
use Mlife\Smsservices\Sender;

Loc::loadMessages(__FILE__);

class Handlers {

    /**
     * @param Event $event
     * @return EventResult
     */
    public static function onAfterEventsAdd(Event $event): EventResult
    {
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
            "NAME" => Loc::getMessage('AWZ_AUTFORM_HANDLERS_MLIFE_SMS_TMPL_NAME')
        );

        //echo'<pre>';print_r($arParam);echo'</pre>';
        //die();

        return new EventResult(
            EventResult::SUCCESS,
            $arParam
        );
    }

    /**
     * Проверка и форматирование номера телефона
     *
     * @param Event $event
     * @return EventResult
     * @throws \Bitrix\Main\LoaderException
     */
    public static function checkPhone(Event $event): ?EventResult
    {

        if(Option::get('awz.autform', 'CHECK_PHONE_MLIFE', 'N', '')!='Y'){
            return null;
        }

        $phone = $event->getParameter('phone');
        $params = $event->getParameter('params');

        $result = new Result;

        if(!Loader::includeModule('mlife.smsservices')){
            $result->addError(new Error(Loc::getMessage('AWZ_AUTFORM_HANDLERS_MLIFE_SMS_MODULE_ERR')));
            return new EventResult(
                EventResult::SUCCESS,
                array('result'=>$result)
            );
        }

        $smsOb = new Sender();
        $check = $smsOb->checkPhoneNumber($phone);
        $phone = $check['phone'];

        $countryCode = '+'.preg_replace('/([^0-9])/','',$params['COUNTRY_CODE']);

        if($countryCode != substr($phone, 0, strlen($countryCode))){
            $check['check'] = false;
        }

        if(!$check['check']){
            $result->addError(new Error(Loc::getMessage('AWZ_AUTFORM_HANDLERS_MLIFE_SMS_PHONE_ERR')));
            return new EventResult(
                EventResult::SUCCESS,
                array('result'=>$result)
            );
        }

        $result->setData(array(
            'phone'=>$phone
        ));

        return new EventResult(
            EventResult::SUCCESS,
            array('result'=>$result)
        );
    }

}