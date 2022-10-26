<?php

namespace Awz\AutForm;

use \Bitrix\Main\Type\DateTime;

class Agents {

    public static function agentDeleteOldCodes(){

        $r = CodesTable::getList(
            array(
                'select'=>array('ID'),
                'filter'=>array('<EXPIRED_DATE'=>DateTime::createFromTimestamp(time()))
            )
        );
        while($data = $r->fetch()){
            CodesTable::delete($data);
        }

        return "\\Awz\\AutForm\\Agents::agentDeleteOldCodes();";
    }

}