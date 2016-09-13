define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function($) {
    'use strict';

    return function (optionsConfig) {
        var releaseNotes = $('<div/>').html(optionsConfig.html).modal({
            modalClass: 'changelog',
            title: $.mage.__('Realex Payments Release Notes'),
            buttons: [{
                text: 'Ok',
                click: function () {
                    this.closeModal();
                }
            }]
        });
        $('#custom-release-notes').on('click', function() {
            releaseNotes.modal('openModal');
        });
    };
});