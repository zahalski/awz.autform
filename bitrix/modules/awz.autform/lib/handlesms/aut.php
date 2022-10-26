<?php

namespace Awz\AutForm\HandleSms;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use Bitrix\Main\Result;
use Bitrix\Main\EventResult;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);
Loc::loadMessages(__DIR__ . '/../../../mlife.smsservices/lib/fields.php');
Loc::loadMessages(__DIR__ . '/../../../main/lib/user.php');

class Aut implements \Awz\AutForm\Interfaces\SmsForm {

    public static function tmplHtml($value=""){

        if(!$value) serialize(array());

        $data = unserialize($value);

        $MCR_EXT = Loc::getMessage('AWZ_AUTFORM_HANDLESMS_DEFAULT_MACROS').'<br><br>';
        $fields = UserTable::getMap();
        foreach($fields as $field){
            /** @var \Bitrix\Main\ORM\Fields\Field $field */
            $langName = Loc::getMessage('MAIN_USER_ENTITY_'.$field->getName().'_FIELD');
            if(!$langName) $langName = Loc::getMessage('AWZ_AUTFORM_HANDLESMS_NOBX');
            $MCR_EXT .= '#USR_'.$field->getName().'# - '.$langName.'<br>';
        }


        //$MCR_EXT = \Mlife\Smsservices\Fields::getOrderCodes(Loc::getMessage("MLIFE_SMSSERVICES_FIELDS_MACROS_NEWORDER"));
        $html = '<tr><td>'.Loc::getMessage("MLIFE_SMSSERVICES_FIELDS_MACROS").'</td><td>'.$MCR_EXT.'</td></tr><tr>';
        $html .= '<td><b>'.Loc::getMessage("MLIFE_SMSSERVICES_FIELDS_TO").'</b></td>';
        $html .= '<td><input type="text" name="PARAMS_PHONE" value="'.$data['PHONE'].'"/></td>';
        $html .= '</tr>';

        $html .= '<td><b>'.Loc::getMessage("MLIFE_SMSSERVICES_FIELDS_APPSMS").'</b></td>';
        if($data['APPSMS'] == 'Y') $checked = ' checked="checked"';
        $html .= '<td><input type="checkbox" name="PARAMS_APPSMS" value="Y"'.$checked.'/></td>';
        $html .= '</tr>';

        return $html;

    }

    public static function tmplHtmlSave($arFields=array()){

        $PARAMS = array(
            "PHONE"=>trim($_REQUEST['PARAMS_PHONE']),
            "APPSMS"=>trim($_REQUEST['PARAMS_APPSMS'])
        );
        $arFields['PARAMS'] = serialize($PARAMS);

        return $arFields;

    }

    public static function OnAfterAddEvent(\Bitrix\Main\Event $event) {

        if(Option::get('awz.autform', 'SEND_SMS_MLIFE', 'N', '')!='Y'){
            return null;
        }

        if(!Loader::includeModule('mlife.smsservices')){
            return null;
        }

        /*
         * params
        'phone'
        'user'
        'code'
        'params'
        'request'
        */

        $eventParams = $event->getParameters();

        $result = new Result;

        $smsEvCode = 'AWZ_'.mb_strtoupper(\Awz\AutForm\Events::SEND_SMS_CODE);
        $arMakros = array(
            '#AWZ_PHONE#'=>$eventParams['phone'],
            '#AWZ_CODE#'=>$eventParams['code'],
            '#EVENT_NAME#'=>Loc::getMessage('AWZ_AUTFORM_HANDLESMS_EVENT_NAME'),
            '#EVENT_CODE#'=>$smsEvCode,
        );

        if($eventParams['user']){

            $userData = \Bitrix\Main\UserTable::getRowById($eventParams['user']);
            foreach($userData as $code=>$value){
                $arMakros['#USR_'.$code.'#'] = $value;
            }

        }

        $res = \Mlife\Smsservices\EventlistTable::getList(
            array(
                'select' => array("*"),
                'filter' => array("=EVENT"=>$smsEvCode,"=ACTIVE"=>"Y","=SITE_ID"=>SITE_ID)
            )
        );

        $findTemplate = false;
        $smsSending = false;
        while($arData = $res->fetch()) {
            $arData['PARAMS'] = unserialize($arData['PARAMS']);
            $findTemplate = true;

            if($arData['PARAMS']['PHONE']){
                $arData['TEMPLATE'] = \Mlife\Smsservices\Events::compileTemplate(
                    $arData['TEMPLATE'], $arMakros
                );
                $phoneAr = str_replace(array_keys($arMakros), $arMakros, $arData['PARAMS']['PHONE']);
                $phoneAr = preg_replace("/([^0-9,])/is","",$phoneAr);
                $phoneAr = explode(",",$phoneAr);
                $sender = $arData['SENDER'] ? $arData['SENDER'] : "";

                foreach($phoneAr as $phone){
                    if(strlen($phone)>7){

                        if(trim($arData['TEMPLATE'])){
                            $smsOb = new \Mlife\Smsservices\Sender();
                            $smsOb->event = $arMakros['#EVENT_CODE#'];
                            $smsOb->eventName = $arMakros['#EVENT_NAME#'];
                            $smsOb->app = ($arData['PARAMS']['APPSMS']=='Y') ? true : false;
                            $smsOb->sendSms($phone, $arData['TEMPLATE'],0,$sender);

                            $smsOb->event = null;
                            $smsOb->eventName = null;
                            $smsSending = true;
                        }

                        break;
                    }
                }
            }

        }

        if(!$findTemplate){
            $result->addError(new Error(Loc::getMessage('AWZ_AUTFORM_HANDLESMS_AUT_NOT_TEMPLATE',array('#CODE#'=>$smsEvCode))));
            return new EventResult(
                EventResult::SUCCESS,
                array('result'=>$result)
            );
        }

        if(!$smsSending){
            $result->addError(new Error(Loc::getMessage('AWZ_AUTFORM_HANDLESMS_AUT_NOT_PHONE')));
            return new EventResult(
                EventResult::SUCCESS,
                array('result'=>$result)
            );
        }

        $result->setData(array(
            'send'=>'ok',
            'message'=>Loc::getMessage('AWZ_AUTFORM_HANDLESMS_AUT_OK_CODE')
        ));

        return new EventResult(
            EventResult::SUCCESS,
            array('result'=>$result)
        );

    }

}