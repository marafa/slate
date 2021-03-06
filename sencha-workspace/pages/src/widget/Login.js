/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('Site.widget.Login', {
    singleton: true,
    requires: [
        'Ext.DomHelper'
    ],
    
    config: {
        loginLinkSelector: '.header-ct nav a[href^="/login"]',
        loginModalId: 'login-modal'
    },

    constructor: function(config) {
        var me = this;
        
        me.callParent(arguments);
        me.initConfig(config);
        
        Ext.onReady(me.onDocReady, me);
    },
    
    onDocReady: function() {
        var me = this,
            body = Ext.getBody(),
            loginModal = me.loginModal = Ext.get(me.getLoginModalId()),
            loginForm = me.loginForm = loginModal && loginModal.down('form');

        if (!loginModal) {
            return;
        }

        loginModal.enableDisplayMode();

        body.on('keyup', 'onBodyKeyup', me);
        Ext.select(me.getLoginLinkSelector()).on('click', 'onLoginLinkClick', me);
        loginModal.down('.modal-close-button').on('click', 'hide', me);
        loginForm.on('submit', 'onLoginSubmit', me);
    },

    hide: function() {
        this.loginModal.hide();
        Ext.getBody().removeCls('blurred');
    },
    
    show: function() {
        Ext.getBody().addCls('blurred');
        this.loginModal.show();
    },

    onBodyKeyup: function(ev, t) {
        if (ev.getKey() == ev.ESC) {
            this.hide();
        }
    },

    onLoginLinkClick: function(ev, t) {
        var me = this;
        
        ev.preventDefault();
        me.show();
        me.loginForm.down('input[autofocus]').focus();
    },

    onLoginSubmit: function(ev, t) {
        var loginForm = this.loginForm;

        ev.preventDefault();
        loginForm.addCls('waiting');

        Ext.Ajax.request({
            url: '/login/json',
            method: 'POST',
            form: loginForm,
            success: function(response) {
                window.location.reload();
            },
            failure: function(response) {
                loginForm.dom.submit();
            }
        });
    }
});