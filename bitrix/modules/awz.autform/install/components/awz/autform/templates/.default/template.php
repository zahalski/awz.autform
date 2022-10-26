<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @var CBitrixComponentTemplate $this
 * @var string $componentPath
 * @var string $templateName
 */

$autFormId = 'awz_autform'.$this->randString();
$arParams['autFormId'] = $autFormId;

$messKeys = array(
    'AWZ_AUTFORM_TMPL_TITLE_AUTH',
    'AWZ_AUTFORM_TMPL_TITLE_AUTHSMS',
    'AWZ_AUTFORM_TMPL_TITLE_REGISTER',
    'AWZ_AUTFORM_TMPL_CLOSE',
    'AWZ_AUTFORM_TMPL_ERR_AJAX',
    'AWZ_AUTFORM_TMPL_LABEL_PHONE',
    'AWZ_AUTFORM_TMPL_LABEL_PASSW',
    'AWZ_AUTFORM_TMPL_LABEL_SMSCODE',
    'AWZ_AUTFORM_TMPL_LABEL_BTN_AUTH',
    'AWZ_AUTFORM_TMPL_LABEL_BTN_CODE',
    'AWZ_AUTFORM_TMPL_LABEL_BTN_CODE2',
    'AWZ_AUTFORM_TMPL_LABEL_BTN_CHECKCODE',
    'AWZ_AUTFORM_TMPL_LOADER'
);

$arLang = array();
foreach($messKeys as $code){
    $arLang[$code] = Loc::getMessage($code) ? Loc::getMessage($code) : $code;
}

?>
<div id="<?=$autFormId?>" class="awz-autform-link-block">
    <?php
    /** @var \Bitrix\Main\Page\FrameBuffered $frame */
    $frame = $this->createFrame($autFormId, false)->begin();
    ?><a id="<?=$autFormId?>_lnk" href="#"><?=Loc::getMessage('AWZ_AUTFORM_TMPL_LINK')?></a><?
    $frame->beginStub();
    $arResult['COMPOSITE_STUB'] = 'Y';
    ?><a id="<?=$autFormId?>_lnk" href="#"><?=Loc::getMessage('AWZ_AUTFORM_TMPL_LINK')?></a><?
    unset($arResult['COMPOSITE_STUB']);
    $frame->end();
    ?>
</div>
<script>
    var options = {
        'theme': '<?=$arParams['THEME']?>',
        'lang':<?=CUtil::PHPToJSObject($arLang);?>
    };
    <?if(empty($arParams['LOGIN_GROUPS']) && !empty($arParams['LOGIN_SMS_GROUPS'])){?>
    options.mode = 'loginsms';
    <?}?>
    <?if(!empty($arParams['LOGIN_GROUPS']) && empty($arParams['LOGIN_SMS_GROUPS'])){?>
    options.mode = 'loginnosms';
    <?}?>
    var <?=$autFormId?> = new AwzAutFormComponent;
    <?=$autFormId?>.siteId = '<?=SITE_ID?>';
    <?=$autFormId?>.autFormId = '<?=$autFormId?>';
    <?=$autFormId?>.ajaxPath = '<?=$componentPath?>/ajax.php';
    <?=$autFormId?>.templateName = '<?=$templateName?>';
    <?=$autFormId?>.signedParameters = '<?=$this->getComponent()->getSignedParameters()?>';
    <?=$autFormId?>.componentName = '<?=$this->getComponent()->getName()?>';
    <?=$autFormId?>.activate(options);
</script>
