<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Errorable;
use Bitrix\Main\Security;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class AwzAutFormComponent extends CBitrixComponent implements Controllerable, Errorable
{
    /** @var ErrorCollection */
    protected $errorCollection;

    /** @var  \Bitrix\Main\HttpRequest */
    protected $request;

    /** @var \Bitrix\Main\Context $context */
    protected $context;


    public $arParams = array();
    public $arResult = array();

    public $userGroups = array();

    public function configureActions()
    {
        return [
            'checkCode' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_POST
                    ]),
                    new ActionFilter\Csrf()
                ],
            ],
            'checkAuth' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_POST
                    ]),
                    new ActionFilter\Csrf()
                ],
            ],
            'getCode' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_POST
                    ]),
                    new ActionFilter\Csrf()
                ],
            ],
        ];
    }

    protected function listKeysSignedParameters()
    {
        return [
            'COUNTRY_CODE',
            'THEME',
            'LOGIN_GROUPS',
            'LOGIN_GROUPS_DEL',
            'LOGIN_SMS_GROUPS',
            'LOGIN_SMS_GROUPS_DEL',
        ];
    }

    public function onPrepareComponentParams($arParams)
    {

        $this->errorCollection = new ErrorCollection();
        $this->arParams = &$arParams;

        if(!$arParams['COUNTRY_CODE']){
            $arParams['COUNTRY_CODE'] = '7';
        }
        if(!$arParams['THEME']){
            $arParams['THEME'] = 'red';
        }
        if(!is_array($arParams['LOGIN_GROUPS'])){
            $arParams['LOGIN_GROUPS'] = array();
        }
        if(!is_array($arParams['LOGIN_GROUPS_DEL'])){
            $arParams['LOGIN_GROUPS_DEL'] = array();
        }
        if(!is_array($arParams['LOGIN_SMS_GROUPS'])){
            $arParams['LOGIN_SMS_GROUPS'] = array();
        }
        if(!is_array($arParams['LOGIN_SMS_GROUPS_DEL'])){
            $arParams['LOGIN_SMS_GROUPS_DEL'] = array();
        }


        return $arParams;
    }

    public function checkUserPassword($userId, $password)
    {

        if(!$this->checkRightGroup('LOGIN_GROUPS')){
            return false;
        }

        $userData = \Bitrix\Main\UserTable::getList(array(
            'select'=>array('PASSWORD'),
            'filter'=>array('=ID'=>$userId)
        ))->fetch();

        if(!$userData) return false;

        if(defined("SM_VERSION") &&
            function_exists('CheckVersion') &&
            CheckVersion( '20.5.399', SM_VERSION)
        ){
            $salt = substr($userData['PASSWORD'], 0, (strlen($userData['PASSWORD']) - 32));
            $realPassword = substr($userData['PASSWORD'], -32);
            $password = md5($salt.$password);
            return ($password == $realPassword);
        }else{
            return Security\Password::equals($userData['PASSWORD'], $password);
        }
    }

    public function findUserFromPhone($phone){

        $preparePhone = htmlspecialcharsEx(trim($phone));
        $phone = preg_replace('/([^0-9])/','',$phone);

        $event = new \Bitrix\Main\Event(
            'awz.autform', \Awz\AutForm\Events::FIND_USER,
            array(
                'preparePhone'=>$preparePhone,
                'phone'=>$phone,
                'params'=>$this->arParams,
                'request'=>$this->request
            )
        );
        $event->send();

        $findUser = 0;
        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $r = $eventResult->getParameters();
                    $r = $r['result'];
                    if($r instanceof \Bitrix\Main\Result){
                        if($r->isSuccess()){
                            $data = $r->getData();
                            if(isset($data['user'])){
                                $findUser = $data['user'];
                            }
                        }else{
                            foreach($r->getErrors() as $error){
                                $this->addError($error);
                            }
                        }
                    }
                }
            }
        }
        if($findUser){
            return $findUser;
        }
        if(!empty($this->getErrors())){
            return null;
        }


        $phoneArray = \Awz\AutForm\Helper::getPhoneCandidates(
            $phone,
            $this->arParams['COUNTRY_CODE']
        );

        $event = new \Bitrix\Main\Event(
            'awz.autform', \Awz\AutForm\Events::AFTER_CREATE_PHONES,
            array(
                'preparePhone'=>$preparePhone,
                'phone'=>$phone,
                'phoneArray'=>$phoneArray,
                'params'=>$this->arParams,
                'request'=>$this->request
            )
        );
        $event->send();

        $phoneFormated = array();
        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $r = $eventResult->getParameters();
                    $r = $r['result'];
                    if($r instanceof \Bitrix\Main\Result){
                        if($r->isSuccess()){
                            $data = $r->getData();
                            if(isset($data['phoneArray'])){
                                $phoneFormated = $data['phoneArray'];
                            }
                        }else{
                            foreach($r->getErrors() as $error){
                                $this->addError($error);
                            }
                        }
                    }
                }
            }
        }
        if(!empty($phoneFormated)) $phoneArray = $phoneFormated;

        $res = \Bitrix\Main\UserTable::getList(
            array(
                'select'=>array('ID'),
                'filter'=>array(
                    'LOGIC'=>'OR',
                    '=PERSONAL_PHONE'=>$phoneArray,
                    '=PERSONAL_MOBILE'=>$phoneArray,
                    '=LOGIN'=>$phoneArray
                ),
                'limit'=>1,
                'order'=>array(
                    'LAST_LOGIN'=>'DESC'
                )
            )
        );
        if($userData = $res->fetch()){

            $this->userGroups = array();

            $r = \Bitrix\Main\UserGroupTable::getList(array(
                'select'=>array('GROUP_ID'),
                'filter'=>array('=USER_ID'=>$userData['ID'])
            ));
            while($data = $r->fetch()){
                $this->userGroups[] = $data['GROUP_ID'];
            }

            return $userData['ID'];
        }
        return null;
    }

    public function checkAuthAction(string $phone, string $password){
        if(!$phone){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_PHONE'), 0);
            return null;
        }
        if(!$password){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_ERR_PASSW'), 0);
            return null;
        }

        if(!Loader::includeModule('awz.autform')){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_INSTALL'), 0);
            return null;
        }

        $phone = $this->checkPhone($phone);

        if(!empty($this->getErrors())) {
            return null;
        }

        $userId = $this->findUserFromPhone($phone);

        if(!$userId){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_USER_NOT_FOUND'),0);
            return null;
        }

        if(!$this->checkRightGroup('LOGIN_GROUPS')){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_ERR_GROUP_LOGIN'),0);
            return null;
        }

        $checkAuth = $this->checkUserPassword($userId, $password);
        if(!$checkAuth){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_ERR_PASSW'),0);
            return null;
        }

        global $USER;
        $USER->Authorize($userId);

        return array(
            'user'=>$userId
        );
    }

    public function checkCodeAction(string $phone, string $code){

        $code = trim(preg_replace('/([^1-9])/is','',$code));

        if(!$phone){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_PHONE'), 0);
            return null;
        }

        if(!$code){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_SMSCODE'), 0);
            return null;
        }

        if(!Loader::includeModule('awz.autform')){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_INSTALL'), 0);
            return null;
        }

        $phone = $this->checkPhone($phone);

        if(!empty($this->getErrors())) {
            return null;
        }

        $userId = $this->findUserFromPhone($phone);

        if(!$userId){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_USER_NOT_FOUND'),0);
            return null;
        }

        $maxCount = intval(Option::get('awz.autform', 'MAX_CHECK', '3', ''));
        $curDate = \Bitrix\Main\Type\DateTime::createFromTimestamp(time());

        $checkRes = \Awz\AutForm\CodesTable::getList(array(
            'select'=>array('*'),
            'filter'=>array(
             '=PHONE'=>$phone,
             '>EXPIRED_DATE'=>$curDate,
            ),
            'order'=>array(
                'ID'=>'DESC'
            )
        ))->fetch();

        if(!$checkRes){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_CODE_NOT_FOUND'), 0);
            return null;
        }

        // required!!! not delete!!! max rate attack (default bitrix session is locked)
        if(bitrix_sessid() != $checkRes['PRM']['csrf']){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_TOKEN_EXPIRED'), 0);
            return null;
        }

        if($maxCount <= intval($checkRes['PRM']['count'])){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_CODE_MAX_CHECK_LIMIT'), 0);
            return null;
        }

        if($checkRes['CODE'] != $code){
            $checkRes['PRM']['count'] = intval($checkRes['PRM']['count']) + 1;
            \Awz\AutForm\CodesTable::update(array('ID'=>$checkRes['ID']), array('PRM'=>$checkRes['PRM']));
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_CODE_ERR'), 0);
            return null;
        }else{

            global $USER;
            $USER->Authorize($userId);

            return array(
                'user'=>$userId
            );

        }

    }

    public function getCodeAction(string $phone){
        if(!$phone){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_PHONE'), 0);
            return null;
        }

        if(!Loader::includeModule('awz.autform')){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_INSTALL'), 0);
            return null;
        }

        $phone = $this->checkPhone($phone);

        if(!empty($this->getErrors())) {
            return null;
        }

        $userId = $this->findUserFromPhone($phone);

        if(!$userId){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_USER_NOT_FOUND'),0);
            return null;
        }

        if(!$this->checkRightGroup('LOGIN_SMS_GROUPS')){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_ERR_GROUP_LOGIN_SMS'),0);
            return null;
        }

        $code = $this->generateCode($phone);

        if(!$code){

            if($this->getErrorByCode(105)){
                return array(
                    'phone'=>$phone,
                    'step'=>'active-code'
                );
            }

            return null;
        }

        $event = new \Bitrix\Main\Event(
            'awz.autform', \Awz\AutForm\Events::SEND_SMS_CODE,
            array(
                'phone'=>$phone,
                'user'=>$userId,
                'code'=>$code,
                'params'=>$this->arParams,
                'request'=>$this->request
            )
        );
        $event->send();

        $result = array();
        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $r = $eventResult->getParameters();
                    $r = $r['result'];
                    if($r instanceof \Bitrix\Main\Result){
                        if($r->isSuccess()){
                            $result = $r->getData();
                        }else{
                            foreach($r->getErrors() as $error){
                                $this->addError($error);
                            }
                        }
                    }
                }
            }
        }

        if(!empty($this->getErrors())) {
            $this->deleteCode($phone, $code);
            return null;
        }

        if(empty($result)){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_EVENT',array('#EVENT_NAME#'=>\Awz\AutForm\Events::SEND_SMS_CODE)),0);
            $this->deleteCode($phone, $code);
            return null;
        }

        if(!isset($result['phone'])){
            $result['phone'] = $phone;
        }

        return $result;

    }

    public function checkPhone($phone){
        $event = new \Bitrix\Main\Event(
            'awz.autform', \Awz\AutForm\Events::CHECK_PHONE,
            array(
                'phone'=>$phone,
                'params'=>$this->arParams,
                'request'=>$this->request
            )
        );
        $event->send();

        $phoneFormated = '';
        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $r = $eventResult->getParameters();
                    $r = $r['result'];
                    if($r instanceof \Bitrix\Main\Result){
                        if($r->isSuccess()){
                            $data = $r->getData();
                            if(isset($data['phone'])){
                                $phoneFormated = $data['phone'];
                            }
                        }else{
                            foreach($r->getErrors() as $error){
                                $this->addError($error);
                            }
                        }
                    }
                }
            }
        }
        if($phoneFormated) $phone = $phoneFormated;
        return $phone;
    }

    public function executeComponent()
    {
        if(!Loader::includeModule('awz.autform'))
        {
            ShowError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_INSTALL'));
            return;
        }

        // output
        if ($this->arParams['AJAX'] == 'Y')
            $this->includeComponentTemplate('ajax_template');
        else
            $this->includeComponentTemplate();
    }

    public function deleteCode($phone, $code){
        $checkRes = \Awz\AutForm\CodesTable::getList(array(
            'select'=>array('ID'),
            'filter'=>array(
                '=PHONE'=>$phone,
                '=CODE'=>$code
            )
        ));
        while($data = $checkRes->fetch()){
            \Awz\AutForm\CodesTable::delete($data);
        }
    }

    public function generateCode($phone){

        if(!$phone){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_MODULE_NOT_PHONE'), 0);
            return false;
        }

        $maxTime = intval(Option::get('awz.autform', 'MAX_TIME', '10', '')) * 60;
        $curDate = \Bitrix\Main\Type\DateTime::createFromTimestamp(time());
        $expiredDate = \Bitrix\Main\Type\DateTime::createFromTimestamp(time()+$maxTime);

        $checkRes = \Awz\AutForm\CodesTable::getList(array(
            'select'=>array('*'),
            'filter'=>array(
                '=PHONE'=>$phone,
                '>EXPIRED_DATE'=>$curDate
            )
        ));
        if($data = $checkRes->fetch()){
            $this->addError(Loc::getMessage('AWZ_AUTFORM_CODE_NOT_EXPIRED', array('#DATE#'=>$data['EXPIRED_DATE']->toString())), 105);
            return null;
        }

        $code = \Bitrix\Main\Security\Random::getStringByCharsets(6, '123456789');
        $r = \Awz\AutForm\CodesTable::add(array(
            'PHONE'=>$phone,
            'CREATE_DATE'=>$curDate,
            'EXPIRED_DATE'=>$expiredDate,
            'PRM'=>array(
                'count'=>0,
                'csrf'=>bitrix_sessid()
            ),
            'CODE'=>$code
        ));

        if($r->isSuccess()){
            return $code;
        }else{
            foreach($r->getErrors() as $error){
                $this->addError($error);
            }
        }

    }

    /**
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function addError($message, $code=0)
    {
        if($message instanceof Error){
            $this->errorCollection[] = $message;
        }elseif(is_string($message)){
            $this->errorCollection[] = new Error($message, $code);
        }
    }

    /**
     * Getting once error with the necessary code.
     * @param string $code Code of error.
     * @return Error
     */
    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    /**
     * Проверяет принадлежность последнего найденного пользователя к одной из групп
     *
     * @param string $param
     * @return bool
     */
    public function checkRightGroup(string $param){

        $groups = $this->arParams[$param];
        $groupsDel = $this->arParams[$param.'_DEL'];

        if(empty($groups)) return false;
        if(empty($this->userGroups)) return false;
        foreach($this->userGroups as $groupId){
            if(in_array($groupId, $groupsDel)) return false;
        }
        foreach($this->userGroups as $groupId){
            if(in_array($groupId, $groups)) return true;
        }
        return false;
    }
}

