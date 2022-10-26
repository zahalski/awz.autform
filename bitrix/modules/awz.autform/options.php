<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
Loc::loadMessages(__FILE__);
global $APPLICATION;
$module_id = "awz.autform";
$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
$zr = "";
if (! ($MODULE_RIGHT >= "R"))
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(Loc::getMessage('AWZ_AUTFORM_OPT_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

Loader::includeModule($module_id);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $MODULE_RIGHT == "W" && strlen($_REQUEST["Update"]) > 0 && check_bitrix_sessid())
{

    Option::set($module_id, "CHECK_PHONE_MLIFE", trim($_REQUEST["CHECK_PHONE_MLIFE"]), "");
    Option::set($module_id, "SEND_SMS_MLIFE", trim($_REQUEST["SEND_SMS_MLIFE"]), "");
    Option::set($module_id, "MAX_TIME", preg_replace('/([^0-9])/','',$_REQUEST["MAX_TIME"]), "");
    Option::set($module_id, "MAX_CHECK", preg_replace('/([^0-9])/','',$_REQUEST["MAX_CHECK"]), "");

}

$aTabs = array();

$aTabs[] = array(
    "DIV" => "edit1",
    "TAB" => Loc::getMessage('AWZ_AUTFORM_OPT_SECT1'),
    "ICON" => "vote_settings",
    "TITLE" => Loc::getMessage('AWZ_AUTFORM_OPT_SECT1')
);

$aTabs[] = array(
    "DIV" => "edit3",
    "TAB" => Loc::getMessage('AWZ_AUTFORM_OPT_SECT3'),
    "ICON" => "vote_settings",
    "TITLE" => Loc::getMessage('AWZ_AUTFORM_OPT_SECT3')
);

$tabControl = new \CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<style>.adm-workarea option:checked {background-color: rgb(206, 206, 206);}</style>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($module_id)?>&lang=<?=LANGUAGE_ID?>&mid_menu=1" id="FORMACTION">

<?
$tabControl->BeginNextTab();
?>

<tr>
    <td><?=Loc::getMessage('AWZ_AUTFORM_OPT_MAX_TIME')?></td>
    <td>
        <?$val = Option::get($module_id, "MAX_TIME", "10", "");?>
        <input type="text" size="35" maxlength="255" value="<?=$val?>" name="MAX_TIME"/>
    </td>
</tr>

<tr>
    <td><?=Loc::getMessage('AWZ_AUTFORM_OPT_MAX_CHECK')?></td>
    <td>
        <?$val = Option::get($module_id, "MAX_CHECK", "3", "");?>
        <input type="text" size="35" maxlength="255" value="<?=$val?>" name="MAX_CHECK"/>
    </td>
</tr>

<?if(Loader::includeModule('mlife.smsservices')){?>
<tr>
    <td width="50%"><?=Loc::getMessage('AWZ_AUTFORM_OPT_CHECK_PHONE_MLIFE')?></td>
    <td>
        <?$val = Option::get($module_id, "CHECK_PHONE_MLIFE", "N","");?>
        <input type="checkbox" value="Y" name="CHECK_PHONE_MLIFE" <?if ($val=="Y") echo "checked";?>></td>
</tr>
<tr>
    <td width="50%"><?=Loc::getMessage('AWZ_AUTFORM_OPT_SEND_SMS_MLIFE')?></td>
    <td>
        <?$val = Option::get($module_id, "SEND_SMS_MLIFE", "N","");?>
        <input type="checkbox" value="Y" name="SEND_SMS_MLIFE" <?if ($val=="Y") echo "checked";?>></td>
</tr>
<?}?>

<tr class="heading">
    <td colspan="2">
        <?=Loc::getMessage('AWZ_EUROPOST_OPT_L_GROUP1')?>
    </td>
</tr>
<tr>
    <td colspan="2" align="center">
        <div class="adm-info-message-wrap">
            <div class="adm-info-message">
                <div> <?=Loc::getMessage('AWZ_EUROPOST_OPT_L_GROUP1_DESC')?></div>
            </div>
        </div>
    </td>
</tr>
<tr>
    <td width="50%"><?=Loc::getMessage('AWZ_EUROPOST_OPT_L_DELPVZ')?></td>
    <td>
        <?$val = "N";?>
        <input type="checkbox" value="Y" name="DELETE_PVZ" <?if ($val=="Y") echo "checked";?>></td>
</tr>
<tr>
    <td width="50%"><?=Loc::getMessage('AWZ_EUROPOST_OPT_L_UPPVZ')?></td>
    <td>
        <?$val = "N";?>
        <input type="checkbox" value="Y" name="UPDATE_PVZ" <?if ($val=="Y") echo "checked";?>></td>
</tr>

<?
$tabControl->BeginNextTab();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
?>

<?
$tabControl->Buttons();
?>
<input <?if ($MODULE_RIGHT<"W") echo "disabled" ?> type="submit" class="adm-btn-green" name="Update" value="<?=Loc::getMessage('AWZ_AUTFORM_OPT_L_BTN_SAVE')?>" />
<input type="hidden" name="Update" value="Y" />
<?$tabControl->End();?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");