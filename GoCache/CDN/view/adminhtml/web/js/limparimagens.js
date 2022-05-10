
/**
 * @api
 */
 define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';


    $.widget('mage.LimparImagens', {
        options: {
            url: '',
            elementId: '',
            successText: '',
            failedText: '',
            fieldMapping: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._connect, this)
            });
        },

        _connect: function () {
            
            var result = this.options.failedText,
                element =  $('#' + this.options.elementId),
                self = this,
                params = {},
                msg = '';

            $.each($.parseJSON(this.options.fieldMapping), function (key, el) {
                params[key] = $('#' + el).val();
            });
            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: params
            }).done(function (response) {
                if (response.success) {
                    alert({
                        content: 'Sucesso ao limpar cache de Imagens.'
                    });
                } else {
                    msg = response.errorMessage;

                    if (msg) {
                        alert({
                            content: msg
                        });
                    }
                }
            }).always(function () {
                
            });

        }

    });
    

});