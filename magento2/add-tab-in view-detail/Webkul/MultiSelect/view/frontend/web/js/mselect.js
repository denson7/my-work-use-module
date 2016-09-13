// js file to apply multiselect js

define(
    [
        'jquery',
        'mage/translate',
        'Magento_Ui/js/modal/alert',
        'Magento_Ui/js/modal/confirm',
        'mage/mage',
        'jquery/ui',
        'jquery/editableMultiselect/js/jquery.editable',
        'jquery/editableMultiselect/js/jquery.multiselect'

    ], function($,$t,alert,confirm) {

        $.widget(
            'webkul.mselectjs', {
                _create: function() {
                    var self = this;

                    /**
                     * intialize multi select for demo tax field
                     */
                    $(self.options.somData.myElement)
                        .multiselect({
                            toggleAddButton:false,
                            addText: $t('Add New Option'),
                            parse: null,
                            mselectInputSubmitCallback:function(value,options) {
                                $('body').trigger('processStart');
                                var select = $(self.options.somData.myElement);
                                /**
                                 * Do your stuff here like saving in the database etc
                                 */
                                response = self.addNewOption(value);

                                /**
                                 * Create the html to add in your multiselect
                                 */
                                select.append('<option value="'+value+'" selected="selected">' + value + '</option>');
                                var mselectItemHtml = $(options.item.replace(/%value%/gi, value)
                                    .replace(/%label%/gi, value)
                                    .replace(/%mselectDisabledClass%|%iseditable%|%isremovable%/gi, '')
                                    .replace(/%mselectListItemClass%/gi, options.mselectListItemClass))
                                    .find('[type=checkbox]')
                                    .attr('checked', true)
                                    .end();
                                var itemsWrapper = select.nextAll('section.block:first')
                                    .find('.' + options.mselectItemsWrapperClass + '');
                                itemsWrapper.children('.' + options.mselectListItemClass + '').length
                                    ? itemsWrapper.children('.' + options.mselectListItemClass + ':last').after(mselectItemHtml)
                                    : itemsWrapper.prepend(mselectItemHtml);
                                $('body').trigger('click');
                                $('body').trigger('processStop');
                            },
                        }).parent().find('.mselect-list')
                        .on(
                            'click.mselect-edit',
                            '.mselect-edit',
                            function(){
                                $('body').trigger('processStart');
                                self.editOption($(this));
                                $('body').trigger('processStop');
                            }
                        )
                        .on(
                            "click.mselect-delete",
                            ".mselect-delete",
                            function(){
                                $('body').trigger('processStart');
                                var optionId = $(this).parent().find('label').find('input').val();
                                self.deleteOption();
                                $(this).parents('.mselect-list-item').remove();
                                $("#tax_customer_class option[value='"+optionId+"']").remove();
                                $('body').trigger('processStop');
                            }
                        );
                },

                /**
                 * addNewOption implement you login here to add new option
                 * @param value new option value
                 */
                addNewOption: function(value) {
                    // implement your login here to do your processing with the value

                    return $.trim(value);


                },

                /**
                 * editOption function to work with edit option
                 */
                editOption: function(element) {
                    // implement your logic here to do your processing
                    var self = element.parent().find('label');
                    var editOption = self.find('span');

                    /**
                     * url ajax url for updating option value
                     * @type String
                     */
                    url = ""
                    /**
                     * override ajax options with your own
                     * @type {Object}
                     */
                    var ajaxoptions = {
                        success : function(result, status) {
                            editOption.html("YOUR RESULT");
                        },
                    };

                    /**
                     * editable is a jquery.editable function which is used for inline
                     * editing it requires two params ajax url and object, the objct has
                     * many options please refer to jquery.editable js
                     */
                    element.parent().find('label').find('span').editable(
                        url,
                        {
                            type:'text',
                            submitdata: {},
                            ajaxoptions: ajaxoptions,
                            indicator: "Updating...",
                            name:'class_name',
                            data:function(value,setting) {

                                return editOption.html();
                            },
                            onerror: function(settings, original, xhr) {
                                alert({content:$t("error occured not able to update class name")});
                            }
                        }
                    );
                    editOption.trigger('click');
                    editOption.editable('destroy');

                },

                deleteOption: function() {
                    // implement your login here to on dele option
                    alert({content: $t("Delete the option create your logic")});
                }

            }

        );

        return $.webkul.mselectjs;
    }
);