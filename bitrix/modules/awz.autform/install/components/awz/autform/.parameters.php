<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

//группы
$rsGroups = CGroup::GetList($by = "c_sort", $order = "asc", array());
$arUserGroup = array();
while($arGroups = $rsGroups->Fetch()){
    $arUserGroup[$arGroups["ID"]] = $arGroups["NAME"];
}

$arComponentParameters = array(
    "GROUPS" => array(
        "DEF" => array(
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_GROUP_DEF'),
        ),
        "LOGIN" => array(
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_GROUP_LOGIN'),
        ),
        "LOGIN_SMS" => array(
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_GROUP_LOGIN_SMS'),
        ),
    ),
    "PARAMETERS" => array(
        "THEME"=>array(
            "PARENT" => "DEF",
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_LABEL_THEME'),
            "TYPE" => "LIST",
            "DEFAULT"=>"red",
            "VALUES" => array('red'=>Loc::getMessage('AWZ_AUTFORM_PARAM_LABEL_THEME_RED')),
        ),
        "COUNTRY_CODE"=>array(
            "PARENT" => "DEF",
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_LABEL_COUNTRY_CODE'),
            "TYPE" => "STRING",
            "DEFAULT"=>"7"
        ),
        "LOGIN_GROUPS" => array(
            "PARENT" => "LOGIN",
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_LABEL_LOGIN_GROUPS'),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arUserGroup,
        ),
        "LOGIN_GROUPS_DEL" => array(
            "PARENT" => "LOGIN",
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_LABEL_LOGIN_GROUPS_DEL'),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arUserGroup,
        ),
        "LOGIN_SMS_GROUPS" => array(
            "PARENT" => "LOGIN_SMS",
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_LABEL_LOGIN_SMS_GROUPS'),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arUserGroup,
        ),
        "LOGIN_SMS_GROUPS_DEL" => array(
            "PARENT" => "LOGIN_SMS",
            "NAME" => Loc::getMessage('AWZ_AUTFORM_PARAM_LABEL_LOGIN_SMS_GROUPS_DEL'),
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => $arUserGroup,
        )
    ),
);