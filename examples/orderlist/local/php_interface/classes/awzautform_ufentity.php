<?php

namespace Awz\Autform\Custom;

class UfEntityTable extends \Bitrix\Main\ORM\Data\DataManager{

    public static function getTableName()
    {
        return 'b_uts_user';
    }

    public static function getMap()
    {
        return array(
            (new \Bitrix\Main\ORM\Fields\IntegerField('VALUE_ID'))->configurePrimary(),
            (new \Bitrix\Main\ORM\Fields\StringField('UF_CHECK_PHONE')),
        );
    }

}