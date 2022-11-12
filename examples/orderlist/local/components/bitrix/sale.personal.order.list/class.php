<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Bitrix\Main\Loader::registerAutoLoadClasses(null, array(
    'CBitrixPersonalOrderListComponent' => '/bitrix/components/bitrix/sale.personal.order.list/class.php',
    '\Awz\Autform\Custom\Helper' => '/local/php_interface/classes/awzautform_custom.php',
    '\Awz\Autform\Custom\UfEntityTable' => '/local/php_interface/classes/awzautform_ufentity.php',
));

use \Awz\Autform\Custom\Helper;

class CBitrixPersonalOrderListComponentCustom extends CBitrixPersonalOrderListComponent {

    protected function prepareFilter(){

        parent::prepareFilter();
        $this->addUsersFilter();

    }

    public function addUsersFilter(){

        global $USER;
        $userId = $USER->GetID();
        if(!$userId) return null;

        if($phone = Helper::getPhone($userId)){
            //make $phone = array('+79215554433','79215554433','9215554433') candidates;
            unset($this->filter['USER_ID']);
            $this->filter[] = array(
                'LOGIC'=>'OR',
                array('=USER_ID'=>$userId, '=PROPERTY.CODE'=>'PHONE'),
                array('=PROPERTY.CODE'=>'PHONE', '=PROPERTY.VALUE'=>$phone),
            );
        }

    }

}