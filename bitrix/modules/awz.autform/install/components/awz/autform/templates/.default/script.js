'use strict';

function AwzAutFormComponent(){}

AwzAutFormComponent.prototype = {
    modeOptions: {
        login: {
            show: ['buttons-aut','group-password'],
            hide: ['buttons-checkcode', 'buttons-getcode', 'buttons-register', 'group-smscode'],
            title: 'AWZ_AUTFORM_TMPL_TITLE_AUTH'
        },
        loginnosms: {
            show: ['buttons-aut','group-password'],
            hide: ['buttons-checkcode', 'buttons-getcode', 'buttons-register', 'group-smscode', 'group-smslink'],
            title: 'AWZ_AUTFORM_TMPL_TITLE_AUTH'
        },
        loginsms: {
            show: ['buttons-getcode'],
            hide: ['buttons-checkcode', 'buttons-aut','group-password', 'buttons-register', 'group-smscode'],
            title: 'AWZ_AUTFORM_TMPL_TITLE_AUTHSMS'
        },
        logincode: {
            show: ['buttons-checkcode','group-smscode'],
            hide: ['buttons-aut', 'buttons-getcode', 'buttons-register', 'group-password'],
            title: 'AWZ_AUTFORM_TMPL_TITLE_AUTHSMS'
        }
    },
    activate: function (options) {
        if(typeof options !== 'object') options = {};
        this.theme = (!!options.theme ? options.theme : false) || 'red';
        this.lang = (!!options.lang ? options.lang : false) || {};
        if(typeof this.lang !==  'object') this.lang = {};
        this.ajaxTimer = 1000;
        this.debug = false;
        this.autForm = BX(this.autFormId);
        this.setMode((!!options.mode ? options.mode : false) || 'login');
        this.initHandlers();
    },
    setMode: function(mode){
        if(!this.modeOptions.hasOwnProperty(mode)) return;
        this.mode = mode;
        this.checkHidden();
    },
    loc: function(code){
        return this.lang.hasOwnProperty(code) ? this.lang[code] : code;
    },
    initHandlers: function(){
        BX.bind(BX(this.autFormId+'_lnk'), 'click', BX.proxy(this.open, this));
    },
    open: function(e){
        if(!!e)
            e.preventDefault();
        this.createPopup();
        BX.append(this.loginForm(), BX(this.autFormId+'_ac'));
        this.checkHidden();
        this.popupSize();
    },
    close: function(e){
        if(!!e)
            e.preventDefault();
        BX(this.autFormId+'_modal').remove();
    },
    checkHidden: function(){

        if(!this.mode) return;

        var k;
        for (k in this.modeOptions[this.mode].show){
            BX.removeClass(BX('awz-autform-form-'+this.modeOptions[this.mode].show[k]),'awz-autform-hide');
        }
        for (k in this.modeOptions[this.mode].hide){
            BX.addClass(BX('awz-autform-form-'+this.modeOptions[this.mode].hide[k]),'awz-autform-hide');
        }

        //console.log(this.loc(this.modeOptions[this.mode].title));
        if(BX(this.autFormId+'_title'))
            BX.adjust(BX(this.autFormId+'_title'), {html: this.loc(this.modeOptions[this.mode].title)});

    },
    checkCodeAction: function(e){
        if(!!e)
            e.preventDefault();
        var parent = this;

        this.showLoader();

        setTimeout(function(){
            BX.ajax.runComponentAction('awz:autform', 'checkCode', {
                mode: 'class',
                data: {
                    phone: BX(parent.autFormId+'_phone').value,
                    code: BX(parent.autFormId+'_smscode').value,
                    signedParameters: parent.signedParameters,
                    method: 'POST'
                }
            }).then(function (response) {
                parent.deleteErr();
                parent.deleteMessage();
                parent.hideLoader();
                if(response.hasOwnProperty('data') && response['data'].hasOwnProperty('user')){
                    window.location.reload();
                }
            }, function (response) {
                parent.showErrors(response);
            });
        },this.ajaxTimer);
    },
    getCodeAction: function(e){
        if(!!e)
            e.preventDefault();
        var parent = this;

        this.showLoader();

        setTimeout(function(){
            BX.ajax.runComponentAction('awz:autform', 'getCode', {
                mode: 'class',
                data: {
                    phone: BX(parent.autFormId+'_phone').value,
                    signedParameters: parent.signedParameters,
                    method: 'POST'
                }
            }).then(function (response) {
                parent.deleteErr();
                parent.deleteMessage();
                parent.hideLoader();
                if(response.hasOwnProperty('data') && response['data'].hasOwnProperty('phone')){
                    BX(parent.autFormId+'_phone').value = response['data']['phone'];
                }
                if(response.hasOwnProperty('data')){
                    parent.showMessage(response['data']);
                }
                parent.setMode('logincode');
            }, function (response) {
                parent.showErrors(response);
                if(response.hasOwnProperty('data') && response['data'].hasOwnProperty('step')){
                    if(response['data']['step'] === 'active-code'){
                        parent.setMode('logincode');
                    }
                }
            });
        },this.ajaxTimer);
    },
    checkAuthAction: function(e){
        if(!!e)
            e.preventDefault();
        var parent = this;

        this.showLoader();

        setTimeout(function(){
            BX.ajax.runComponentAction('awz:autform', 'checkAuth', {
                mode: 'class',
                data: {
                    phone: BX(parent.autFormId+'_phone').value,
                    password: BX(parent.autFormId+'_password').value,
                    signedParameters: parent.signedParameters,
                    method: 'POST'
                }
            }).then(function (response) {
                parent.deleteErr();
                parent.deleteMessage();
                parent.hideLoader();
                if(response.hasOwnProperty('data') && response['data'].hasOwnProperty('user')){
                    window.location.reload();
                }
            }, function (response) {
                parent.showErrors(response);
            });
        },this.ajaxTimer);

    },
    showLoader: function(){
        BX.append(
            BX.create('div',
                {
                    attrs:{
                        id: this.autFormId+'_preloader',
                        className: 'awz-autform-preloader',
                    },
                    html: this.loader_template()
                }
            ),
            BX(this.autFormId+'_content')
        );
        this.popupSize();
    },
    hideLoader: function(){
        if(BX(this.autFormId+'_preloader')){
            BX(this.autFormId+'_preloader').remove();
        }
        var parent = this;
        this.popupSize();
        setTimeout(function(){
            parent.popupSize();
        },10);

    },

    deleteErr: function(){
        if(!!BX(this.autFormId+'_err')) BX(this.autFormId+'_err').remove();
    },
    deleteMessage: function(){
        if(!!BX(this.autFormId+'_msg')) BX(this.autFormId+'_msg').remove();
    },
    showErrors: function(data){
        if(data.hasOwnProperty('status') && data.status === 'error'){
            this.deleteErr();
            this.deleteMessage();
            BX.prepend(BX.create({
                tag: 'div',
                props: {
                    id: this.autFormId+'_err',
                    className: 'awz-autform-errors'
                }
            }), BX(this.autFormId + '_form'));
            if(!data.hasOwnProperty('errors')){
                data['errors'] = [{
                    code: 0,
                    customData: null,
                    message: this.loc('AWZ_AUTFORM_TMPL_ERR_AJAX')
                }];
            }
            var k;
            for(k in data['errors']){
                var err = data['errors'][k];
                if(!this.debug && err.message.indexOf('php')>-1){
                    //enabled debug mode to production
                }else{
                    BX.append(
                        BX.create({
                            tag: 'div',
                            props: {
                                className: 'awz-autform-error-row'
                            },
                            text: err.message
                        }),
                        BX(this.autFormId + '_err')
                    );
                }
            }
        }
        this.hideLoader();
    },
    showMessage: function(data){
        if(data.hasOwnProperty('message')){
            this.deleteMessage();
            BX.prepend(BX.create({
                tag: 'div',
                props: {
                    id: this.autFormId+'_msg',
                    className: 'awz-autform-messages'
                }
            }), BX(this.autFormId + '_form'));
            BX.append(
                BX.create({
                    tag: 'div',
                    props: {
                        className: 'awz-autform-message-row'
                    },
                    text: data.message
                }),
                BX(this.autFormId + '_msg')
            );
        }
        this.hideLoader();
    },
    createPopup: function(){
        BX.append(BX.create('div',{attrs:{id: this.autFormId+'_modal'}, props: {className: 'awz-autform-theme-'+this.theme}}), BX(document.body));
        BX.adjust(BX(this.autFormId+'_modal'), {html: this.template()});
        BX.bind(BX(this.autFormId+'_close'), 'click', BX.proxy(this.close, this));
        this.popupSize();
    },
    popupSize: function(){
        var h = BX.height(window);
        var w = BX.width(window);
        if(w > 860) {
            w = Math.ceil(w*0.8);
            h = Math.ceil(h*0.8);

            var h2 = BX.height(BX(this.autFormId+'_ac')) + BX.height(BX.findChild(BX(this.autFormId+'_content'), {'className':'awz-autform-modal-header'}, true, false));
            if(h2 && h > h2) h = h2;

            BX.adjust(
                BX(this.autFormId+'_content'), {
                    style: {
                        'margin-top':Math.ceil((BX.height(window)-h)/2)+'px',
                        'width':w+'px',
                        'height': h+'px'
                    }
                }
            );
        }else{
            BX.addClass(BX(this.autFormId+'_close'),'awz-ep-close-mobile');
            w = Math.ceil(w);
            h = Math.ceil(h);
            BX.adjust(
                BX(this.autFormId+'_content'),
                {
                    style: {
                        'width':w+'px',
                        'height': h+'px'
                    }
                }
            );
        }
    },
    loader_template: function(){
        var loader_mess = this.loc('AWZ_AUTFORM_TMPL_LOADER');
        return '<div class="awz-autform-preload"><div class="awz-autform-load">'+loader_mess+'</div></div>';
    },
    template: function(){

        var ht = '<div class="awz-autform-modal-content-bg"></div>' +
            '<a id="'+this.autFormId+'_close" class="awz-autform-close" href="#"><div>\n' +
            '        <div class="awz-autform-close-leftright"></div>\n' +
            '        <div class="awz-autform-close-rightleft"></div>\n' +
            '        <span class="awz-autform-close-close-btn">'+this.loc('AWZ_AUTFORM_TMPL_CLOSE')+'</span>\n' +
            '    </div></a>' +
            '<div class="awz-autform-modal-content">' +
                '<div id="'+this.autFormId+'_content" class="awz-autform-modal-content-wrap">'+
                    '<div class="awz-autform-modal-header" id="'+this.autFormId+'_title">'+
                    ''+this.loc('AWZ_AUTFORM_TMPL_TITLE_AUTH')+
                    '</div>'+
                    '<div class="awz-autform-modal-body">' +
                        '<div id="'+this.autFormId+'_ac" class="awz-autform-contentWrap"></div>' +
                    '</div>'+
                '</div>' +
            '</div>';

        return ht;
    },
    loginForm: function(){

        var form = BX.create({
            tag: 'form',
            props: {
                id: this.autFormId + '_form'
            },
            children: [
                BX.create({
                    tag: 'div',
                    props: {
                        className: "awz-autform-form-group"
                    },
                    children: [
                        BX.create({
                            tag: 'label',
                            props: {for: this.autFormId+'_phone'},
                            text: this.loc('AWZ_AUTFORM_TMPL_LABEL_PHONE')
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className:'awz-autform-inp-wrap'},
                            children: [
                                BX.create({
                                    tag: 'input',
                                    props: {
                                        id: this.autFormId+'_phone',
                                        type:'text',
                                        className:'awz-autform-form-control',
                                        name: 'awz-phone',
                                    }
                                }),
                            ]
                        }),

                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: "awz-autform-form-group",
                        id: "awz-autform-form-group-password"
                    },
                    children: [
                        BX.create({
                            tag: 'label',
                            props: {for: this.autFormId+'_password'},
                            children: [
                                BX.create({
                                    tag: 'span',
                                    text: this.loc('AWZ_AUTFORM_TMPL_LABEL_PASSW'),
                                }),
                                BX.create({
                                    tag: 'a',
                                    props: {
                                        href: '#',
                                        id: 'awz-autform-form-group-smslink',
                                    },
                                    events: {
                                        click: BX.proxy(this.getCodeAction, this)
                                    },
                                    text: this.loc('AWZ_AUTFORM_TMPL_LABEL_BTN_CODE2'),
                                })
                            ]
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className:'awz-autform-inp-wrap'},
                            children: [
                                BX.create({
                                    tag: 'input',
                                    props: {
                                        id: this.autFormId+'_password',
                                        type:'password',
                                        className:'awz-autform-form-control',
                                        name: 'awz-password',
                                    }
                                }),
                            ]
                        }),

                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: "awz-autform-form-group",
                        id: "awz-autform-form-group-smscode"
                    },
                    children: [
                        BX.create({
                            tag: 'label',
                            props: {for: this.autFormId+'_smscode'},
                            children: [
                                BX.create({
                                    tag: 'span',
                                    text: this.loc('AWZ_AUTFORM_TMPL_LABEL_SMSCODE'),
                                }),
                                BX.create({
                                    tag: 'a',
                                    props: {
                                        href: '#'
                                    },
                                    events: {
                                        click: BX.proxy(this.getCodeAction, this)
                                    },
                                    text: this.loc('AWZ_AUTFORM_TMPL_LABEL_BTN_CODE2'),
                                })
                            ]
                        }),
                        BX.create({
                            tag: 'div',
                            props: {className:'awz-autform-inp-wrap'},
                            children: [
                                BX.create({
                                    tag: 'input',
                                    props: {
                                        id: this.autFormId+'_smscode',
                                        type:'text',
                                        className:'awz-autform-form-control',
                                        name: 'awz-smscode',
                                    }
                                }),
                            ]
                        }),

                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: "awz-autform-form-buttons",
                        id: "awz-autform-form-buttons-aut"
                    },
                    children: [
                        BX.create({
                            tag: 'button',
                            props: {
                                id: this.autFormId+'_auth',
                                className:'awz-autform-form-control',
                            },
                            events: {
                                click: BX.proxy(this.checkAuthAction, this)
                            },
                            text: this.loc('AWZ_AUTFORM_TMPL_LABEL_BTN_AUTH')
                        }),
                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: "awz-autform-form-buttons",
                        id: "awz-autform-form-buttons-register"
                    },
                    children: [
                        BX.create({
                            tag: 'button',
                            props: {
                                id: this.autFormId+'_auth',
                                className:'awz-autform-form-control',
                            },
                            events: {
                                click: BX.proxy(this.getCodeRegisterAction, this)
                            },
                            text: this.loc('AWZ_AUTFORM_TMPL_LABEL_BTN_CODE')
                        }),
                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: "awz-autform-form-buttons",
                        id: "awz-autform-form-buttons-getcode"
                    },
                    children: [
                        BX.create({
                            tag: 'button',
                            props: {
                                id: this.autFormId+'_send',
                                className:'awz-autform-form-control',
                            },
                            events: {
                                click: BX.proxy(this.getCodeAction, this)
                            },
                            text: this.loc('AWZ_AUTFORM_TMPL_LABEL_BTN_CODE')
                        }),
                    ]
                }),
                BX.create({
                    tag: 'div',
                    props: {
                        className: "awz-autform-form-buttons",
                        id: "awz-autform-form-buttons-checkcode"
                    },
                    children: [
                        BX.create({
                            tag: 'button',
                            props: {
                                id: this.autFormId+'_send',
                                className:'awz-autform-form-control',
                            },
                            events: {
                                click: BX.proxy(this.checkCodeAction, this)
                            },
                            text: this.loc('AWZ_AUTFORM_TMPL_LABEL_BTN_CHECKCODE')
                        }),
                    ]
                })
            ]
        });

        return form;

    }
};