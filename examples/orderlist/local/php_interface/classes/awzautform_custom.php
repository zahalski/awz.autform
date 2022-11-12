<?php

namespace Awz\Autform\Custom;

use Awz\Autform\Custom\UfEntityTable;

class Helper {

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
        if($phone = $cp->get('UF_CHECK_PHONE')){
            return $phone;
        }
        return null;
    }

}