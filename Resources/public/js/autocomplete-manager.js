/**
 * This plugin is meant to be used with the corresponding AutocompleteType
 *
 * @see Charlyp\AutocompleteBundle\Form\AutocompleteType
 *
 * @requires jQuery
 *
 * @author Charles Pourcel <ch.pourcel@gmail.com>
 */
!function ($) {

    "use strict";

    var AutocompleteManager = function (element, options) {
        this.$element = null;

        this.init(element, options);
    }

    AutocompleteManager.DEFAULTS = {
        'url': '',
        'urlParamName': 'search',
        'optionLabelKey': '',
        'valueKey' : 'value',
        'targetValueKey': null,
        'target': null,
        'dataHolderName': 'autocomplete-result',
        'selectEventName' : 'autocompletemanager.select'
    }

    AutocompleteManager.prototype.init = function (element, options) {
        this.$element = $(element);

        this.options = this.getOptions(options);

        var that = this,
            $target = $('#' + that.options.target);

        this.$element.autocomplete({
            source: function(request, response) {
                var url = that.options.url.replace('__'+ that.options.urlParamName +'__', request.term);
                $.ajax({
                    url: url,
                    datatype: "json",
                    success: function(data) {
                        response($.map(data, function(item) {
                            var result = {
                                id: item.id,
                                label: item[that.options.optionLabelKey],
                                value: (item[that.options.valueKey])? item[that.options.valueKey] : item[that.options.optionLabelKey],
                                data: item
                            };

                            return result;
                        }))
                    }
                })
            },
            open: function() {
                $('li.ui-menu-item:nth-child(2n+1)').addClass('ac_odd');
            },
            select: function(event, data) {
                //Store the whole item in the data of the autocomplete element
                that.$element.data(that.options.dataHolderName, data.item.data);
                //Custom event providing all the data retrieved from the selected value
                that.$element.trigger(that.options.selectEventName, that.$element.data(that.options.dataHolderName));

                if (that.options.target) {
                    $target.attr('value', data.item ? data.item.data[that.options.targetValueKey] : null);
                }
            },
            change: function(event, ui) {},
            messages: {
                noResults: '',
                results: function() {}
            }
        }).data( "autocomplete" )._renderItem = this.getRenderer;
    }

    AutocompleteManager.prototype.getRenderer = function(ul, item) {
        return $('<li></li>')
            .append('<a>' + item.label + '</a>')
            .appendTo(ul);
    }

    AutocompleteManager.prototype.getDefaults = function () {
        return AutocompleteManager.DEFAULTS;
    }

    AutocompleteManager.prototype.getOptions = function (options) {
        options = $.extend({}, this.getDefaults(), this.$element.data(), options);

        return options;
    }

    // Autocomplete Manager Plugin Definition
    $.fn.manageAutocomplete = function () {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('autocomplete');

            if (!data) $this.data('autocomplete', new AutocompleteManager(this));
        })
    };

    // Autocomplete Manager DATA-API
    $(document).ready(function () {
        $('[data-toggle="autocomplete"]').manageAutocomplete();
    });
}(window.jQuery);
