<?php

namespace Awz\AutForm;

class Helper {

    /**
     * Возвращает массив кандидатов номера для поиска
     *
     * @param $phone
     * @param int $countryCode
     * @return array
     */
    public static function getPhoneCandidates($phone, $countryCode=7){

        $phoneArray = array(
            $phone
        );
        if(mb_substr($phone, 0, 2) == 80){
            $phoneArray[] = '+'.$countryCode.mb_substr($phone, 2);
        }elseif(mb_substr($phone, 0, 1) == 8){
            $phoneArray[] = '+'.$countryCode.mb_substr($phone, 1);
        }else{
            $phoneArray[] = '+'.$phone;
        }
        $candidateCountryCode = mb_substr($phone, 0, mb_strlen($countryCode));
        if($countryCode == $candidateCountryCode){
            $phoneArray[] = mb_substr($phone, mb_strlen($countryCode));
        }

        return $phoneArray;

    }

}