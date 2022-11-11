<?
$moduleId = "awz.autform";
if(IsModuleInstalled($moduleId)) {
    $updater->CopyFiles(
        "install/components/awz/autform",
        "components/awz/autform",
        true,
        true
    );
    $eventManager = \Bitrix\Main\EventManager::getInstance();
    $eventManager->registerEventHandler(
        $moduleId, 'onSendSmsCode',
        $moduleId, '\Awz\AutForm\Handlers', 'onSendSmsCode'
    );
    $eventManager->registerEventHandler(
        $moduleId, 'onCheckCode',
        $moduleId, '\Awz\AutForm\Handlers', 'onCheckCode'
    );
}