<?
$moduleId = "awz.autform";
if(IsModuleInstalled($moduleId)) {
    $updater->CopyFiles(
        "install/components/awz/autform",
        "components/awz/autform",
        true,
        true
    );
}