(function($){
	$.request = {
        indicator: function(symbol) {
            $(this).data('indicator', symbol);
        }
        onrequest: function(callback) {
            $(this).data('onrequest', callback);
        }
        do: function(options) {

            var defaults = {
                tab: '',
                event: '',
                value: '',
                callback: null
                },
                settings = $.extend({}, defaults, options);

            $($(this).data('indicator')).show();

            /*
             * prepare and make the PHP ajax request
             * you can provide a callback that will be called if request was successful:
             * e.g. for clean-up or result messages
             */

            // set POST based on draft or size of value!
            post = draft != '' || value.length > 200;
            var data = {'tab' : tab, 'event' : event, 'value' : value,
                        'draft' : draft }
            if (post === true) {
                $.post(ajax_file,
                    data,
                    function(response) {
                        success = render(response);
                        if (success && callback !== false) callback();
                    },
                    'json'
                );
            } else {
                $.getJSON(ajax_file,
                    data,
                    function(response) {
                        success = render(response);
                        if (success && callback !== false) callback();
                    }
                );
            }
        }
        /*
         * generic ajax response function
         * renders any returned response data and updates address where necessary
         */
        var render = function(response) {
            $($(this).data('indicator')).hide()
            if (response == 'action_only') return true;
            if (response !== undefined && response !== null) {
                update_view(response);
                if (response.address != '' && $.address.value() != response.address) {
                    $.address.value(response.address);
                }
                return true;
            } else {
                return false;
            }
        }

      // returns the jQuery object to allow for chainability.
      return this;
    }
})(jQuery);

