<?php

//размещаем в init.php

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    'awz.autform', 'onSendSmsCode',
    array('handlersAutForm','onSendSmsCode')
);

class handlersAutForm {

    public static function onSendSmsCode(Bitrix\Main\Event $event)
    {

        $result = new Bitrix\Main\Result;

        $eventParams = $event->getParameters();

        /* параметры
         * 'phone'=>$phone,
         * 'user'=>$userId,
         * 'code'=>$code,
         * 'params'=>$parameters,
         * 'request'=>$this->request
         * */

        $phone = $eventParams['phone'];
        $phone = preg_replace('/([^0-9])/','', $phone);

        if($phone == '79217776655'){
            $result->addError(new Bitrix\Main\Error('Ваш номер в черном списке'));
        }elseif(strlen($phone)!=11){
            $result->addError(new Bitrix\Main\Error('Номер должен содержать 11 символов'));
        }else{
            $result->setData(array(
                'send'=>'ok',
                'message'=>'Код отправлен, на номер '.$eventParams['phone'].', код: '.$eventParams['code']
            ));
        }

        return new Bitrix\Main\EventResult(
            Bitrix\Main\EventResult::SUCCESS,
            array('result'=>$result)
        );

    }

}