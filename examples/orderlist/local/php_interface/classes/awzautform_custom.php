<?php

namespace Awz\Autform\Custom;

use Awz\Autform\Custom\UfEntityTable;
use Awz\AutForm\Helper as OHelper;
use Bitrix\Main\Result;

class Helper {

    /*
     * код страны для номера телефона по умолчанию
     * */
    const PARAM_COUNTRY_CODE = '7';
    /*
     * Код свойства заказа, в котором хранится номер телефона
     * */
    const ORDER_PHONE_FIELD_CODE = 'PHONE';
    /*
     * код пользовательского свойства у пользователя битрикса
     * для записи подтвержденного номера телефона
     * */
    const USER_UF_CHECKED_CODE = 'UF_CHECK_PHONE';

    public static function getPhone($userId){
        if(!$userId) return null;
        $currentUser = \Bitrix\Main\UserTable::query()
            ->registerRuntimeField(
                (new \Bitrix\Main\Entity\ReferenceField(
                    'CP',
                    UfEntityTable::class,
                    \Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.VALUE_ID')
                ))->configureJoinType(\Bitrix\Main\ORM\Query\Join::TYPE_LEFT)
            )
            ->addSelect('ID')
            ->addSelect('CP')
            ->where('ID', $userId)->fetchObject();

        if(!$currentUser) return null;
        if(!$cp = $currentUser->get('CP')) return null;
        if($phone = $cp->get(self::USER_UF_CHECKED_CODE)){
            return $phone;
        }
        return null;
    }

    public static function getPhonesCandidate($phone, $countryCode=Helper::PARAM_COUNTRY_CODE){

        $preparePhone = htmlspecialcharsEx(trim($phone));
        $phone = preg_replace('/([^0-9])/','',$phone);

        $phoneArray = OHelper::getPhoneCandidates(
            $phone,
            $countryCode
        );

        $event = new Event(
            'awz.autform', Events::AFTER_CREATE_PHONES,
            array(
                'preparePhone'=>$preparePhone,
                'phone'=>$phone,
                'phoneArray'=>&$phoneArray,
                'params'=>array('COUNTRY_CODE'=>$countryCode)
            )
        );
        $event->send();

        $phoneFormated = array();
        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == EventResult::SUCCESS) {
                    if($eventResultData = $eventResult->getParameters()){
                        if(!isset($eventResultData)) continue;
                        $r = $eventResultData['result'];
                        if($r instanceof Result){
                            if($r->isSuccess()){
                                $data = $r->getData();
                                if(isset($data['phoneArray'])){
                                    //если нужно прекратить применение обработчиков
                                    $phoneFormated = $data['phoneArray'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
        if(!empty($phoneFormated)) $phoneArray = $phoneFormated;

        return $phoneArray;
    }

}