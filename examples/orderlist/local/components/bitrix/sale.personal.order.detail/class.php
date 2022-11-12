<?php

Bitrix\Main\Loader::registerAutoLoadClasses(null, array(
    'CBitrixPersonalOrderDetailComponent' => '/bitrix/components/bitrix/sale.personal.order.detail/class.php',
    '\Awz\Autform\Custom\Helper' => '/local/php_interface/classes/awzautform_custom.php',
    '\Awz\Autform\Custom\UfEntityTable' => '/local/php_interface/classes/awzautform_ufentity.php',
));

use \Awz\Autform\Custom\Helper;

class CBitrixPersonalOrderDetailComponentCustom extends CBitrixPersonalOrderDetailComponent {

    protected function checkAuthorized(){

        global $USER;
        $userId = $USER->GetID();
        if(!$userId) return parent::checkAuthorized();

        if(!\Bitrix\Main\Loader::includeModule('awz.autform'))
            return parent::checkAuthorized();

        $checkRight = false;
        if($phone = Helper::getPhone($userId)){
            //make $phone = array('+79215554433','79215554433','9215554433') candidates;
            $phones = Helper::getPhonesCandidate($phone);
            $this->loadOrder(urldecode(urldecode($this->arParams["ID"])));
            if($this->order){
                $propertyCollection = $this->order->getPropertyCollection();
                if($propertyCollection){
                    $propertiesCandidate = $propertyCollection->getItemsByOrderPropertyCode('PHONE');
                    foreach($propertiesCandidate as $property){
                        $value = $property->getValue();
                        if(in_array($value, $phones)){
                            $checkRight = true;
                            break;
                        }
                    }
                }
            }
        }

        if($checkRight) return null;

        return parent::checkAuthorized();
    }

}