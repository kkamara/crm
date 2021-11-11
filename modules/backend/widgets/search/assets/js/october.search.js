/*
 * Search Widget
 */
+function ($) { "use strict";

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype;

    var SearchWidget = function (element, options) {
        this.$el = $(element);
        this.options = options || {};

        this.$form = this.$el.closest('form');
        this.$triggerEl = !!this.$form.length ? this.$form : this.$el;
        this.$input = $('[data-search-input]', this.$el);
        this.$clearBtn = $('[data-search-clear]', this.$el);
        this.extraData = null;

        $.oc.foundation.controlUtils.markDisposable(element);
        Base.call(this);

        this.init();
    }

    SearchWidget.prototype = Object.create(BaseProto);
    SearchWidget.prototype.constructor = SearchWidget;

    SearchWidget.DEFAULTS = {
    }

    SearchWidget.prototype.init = function() {
        this.$triggerEl.on('ajaxComplete', this.proxy(this.toggleClearButton));
        this.$clearBtn.on('click', this.proxy(this.clearInput));

        this.toggleClearButton();
    }

    SearchWidget.prototype.dispose = function() {
        this.$triggerEl.off('ajaxComplete', this.proxy(this.toggleClearButton));
        this.$el.off('ajaxSetup');

        this.$el.removeData('oc.searchwidget');

        this.$el = null;

        // In some cases options could contain callbacks,
        // so it's better to clean them up too.
        this.options = null;

        BaseProto.dispose.call(this);
    }

    SearchWidget.prototype.clearInput = function() {
        this.$input.val('').request();
    }

    SearchWidget.prototype.toggleClearButton = function() {
        if (this.$input.val().length) {
            this.$clearBtn.show();
        }
        else {
            this.$clearBtn.hide();
        }
    }

    SearchWidget.prototype.linkToListWidget = function(elId) {
        this.$el.on('ajaxSetup', function (evt, data) {
            var $widget = $('#'+elId+' > .control-list:first');
            if (!$widget.data('oc.listwidget')) {
                return;
            }

            data.options.data.allChecked = $widget.listWidget('getAllChecked');
        });
    }

    // SEARCH WIDGET PLUGIN DEFINITION
    // ============================

    var old = $.fn.searchWidget

    $.fn.searchWidget = function (option) {
        var args = Array.prototype.slice.call(arguments, 1), result

        this.each(function () {
            var $this   = $(this)
            var data    = $this.data('oc.searchwidget')
            var options = $.extend({}, SearchWidget.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('oc.searchwidget', (data = new SearchWidget(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
      }

    $.fn.searchWidget.Constructor = SearchWidget

    // SEARCH WIDGET NO CONFLICT
    // =================

    $.fn.searchWidget.noConflict = function () {
        $.fn.searchWidget = old;
        return this;
    }

    // SEARCH WIDGET HELPERS
    // =================

    if ($.oc === undefined) {
        $.oc = {};
    }

    // SEARCH WIDGET DATA-API
    // ==============

    $(document).render(function(){
        $('[data-control="searchwidget"]').searchWidget();
    })

}(window.jQuery);
