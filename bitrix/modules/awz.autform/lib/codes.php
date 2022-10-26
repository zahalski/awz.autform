<?php

namespace Awz\AutForm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

class CodesTable extends Entity\DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_awz_autform_codes';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                    'primary' => true,
                    'autocomplete' => false,
                    'title'=>Loc::getMessage('AWZ_AUTFORM_CODES_FIELDS_ID')
                )
            ),
            new Entity\StringField('PHONE', array(
                    'required' => true,
                    'title'=>Loc::getMessage('AWZ_AUTFORM_CODES_FIELDS_PHONE')
                )
            ),
            new Entity\StringField('CODE', array(
                    'required' => true,
                    'title'=>Loc::getMessage('AWZ_AUTFORM_CODES_FIELDS_CODE')
                )
            ),
            new Entity\DatetimeField('CREATE_DATE', array(
                    'required' => true,
                    'title'=>Loc::getMessage('AWZ_AUTFORM_CODES_FIELDS_CREATE_DATE')
                )
            ),
            new Entity\DatetimeField('EXPIRED_DATE', array(
                    'required' => true,
                    'title'=>Loc::getMessage('AWZ_AUTFORM_CODES_FIELDS_EXPIRED_DATE')
                )
            ),
            new Entity\StringField('PRM', array(
                    'required' => false,
                    'serialized'=>true,
                    'title'=>Loc::getMessage('AWZ_AUTFORM_CODES_FIELDS_PRM')
                )
            ),
        );
    }

}