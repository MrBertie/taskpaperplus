(function() {
    // alert message colours
    var YELLOW          = "#FFFF88";
    var ORANGE          = "#FF7400";
    var GREEN           = "#CDEB8B";
    var BLUE            = "#C3D9FF";
    var GREY            = "#D7D5D4";

    var ajax_file       = '';
    var task_view       = '';
    var edit_view       = '';
    var text_area       = '';

    var messages        = [];
    var add_msg         = [];
    var edit_msg        = [];
    var erase_msg       = [];
    var arch_msg        = [];
    var all_arch_msg    = [];
    var rename_msg      = '';
    var remove_msg      = '';
    var create_msg      = '';
    var search_msg      = '';
    var editable_tip    = '';
    var save_tip        = '';
    var cancel_tip      = '';

    var search_box;

    var index_load;
    var task_prefix     = '';

    /**
     * Initialise all global vars once document has loaded
     */
    var init = function() {
        ajax_file       = "index.php";
        task_view       = $("#task-view");
        edit_view       = $("#edit-view");
        text_area       = $("#edit-view>textarea");

        messages        = $("#alert-messages").val().split("|");
        add_msg         = [messages[0], BLUE];
        edit_msg        = [messages[1], YELLOW];
        erase_msg       = [messages[2], ORANGE];
        arch_msg        = [messages[3], GREEN];
        all_arch_msg    = [messages[4], GREEN];
        rename_msg      = messages[5];
        remove_msg      = messages[6];
        create_msg      = messages[7];
        editable_tip    = messages[8];
        save_tip        = messages[9];
        cancel_tip      = messages[10];

        search_box = $("#search-box");

        // set initial page address correctly (deep link after the #)
        // this only happens on a page rebuild (i.e. page refresh or via user typed URL)
        var page_address = $("#page-address").val();
        $.address.value(page_address);

        // signals whether this was a full page load/rebuild or not
        index_load = $('#page-load');

        task_prefix = $('#task-prefix').val();

        $('body').data('editable', null);
    }

    /**
     * All dynamic events
     *
     */
    var add_events = function() {

        $("#purge-session").live("click", function() {
            request('', 'purgesession', '', '', function() {
                show_message(['Session cleared! Reloading...', GREEN]);
                setTimeout("window.location.reload()", 1500);
                index_load.val('false');
            });
        });

        $("#purge-cache").live("click", function() {
            request('', 'purgecache', '', '', function() {
                show_message(['Cache cleared!', GREEN]);
            });
        });

        $('#footer select').live('change', function() {
            request('', 'lang', this.value, '', function() {
                show_message(['Language changed! Reloading...', GREEN]);
                setTimeout("window.location.reload()", 1000);
            });
        });

        $("#home").live("click", _reset);

        $("#edit-button").live("click", function() {
            request('', 'edit', '', '', function() {
                $('#edit-view textarea')
                    .bind('keydown', 'ctrl+return meta+return', _save_edits)
                    .bind('keydown', 'esc', _reset);

            });
        });

        /* this is the 'Save' button, for editing area */
        $("#edit-view input.save-button").live("click", _save_edits);

        function _save_edits() {
            request('', 'save', text_area.val());
            $('#edit-view textarea')
                .unbind('keydown', 'ctrl+return meta+return', _save_edits)
                .unbind('keydown', 'esc', _reset);
        }

        $("#edit-view input.cancel-button").live("click", _reset);

        function _reset() {
            request('', 'all', '');
        }

        $(".tag").live("click", function() {
            request('', 'tag', this.innerHTML);
        });

        function _search_box() {
            search_box.data('can_reset', true);
            $("#placeholder").fadeOut(600);
            $("#reset-search").show();
        }
        $("#placeholder").live("click", function() {
            _search_box();
            search_box.focus();
        })
        search_box.live("click", function() {
            _search_box();
        });
        search_box.live("focus", function() {
            _search_box();
        });
        search_box.live("blur", function() {
            if ($(this).val() == '') {
                $("#reset-search").hide();
                $("#placeholder").fadeIn(1200);
                search_box.data('can_reset', false);
            }
        });

        search_box.live("keyup", function(event) {
            if (event.keyCode == 13) {
                var expression = search_box.val();
                // check for create tasks first (must have a space following!)
                if (expression.substr(0, 2) == task_prefix + " ") {
                    request('', 'add', expression, '', function() {
                        show_message(add_msg);
                        search_box.val('');
                    });
                // then search expressions
                } else if (expression != "") {
                    request('', 'search', expression);
                // enter in a blank box == reset (common practice)
                } else {
                    request('', 'all', '');
                }
            }
        });
        $("#reset-search").live("click", function() {
            if (search_box.data('can_reset') == true) {
                search_box.val('');
                search_box.trigger('blur');
            }
        });

        $("#tasks>li").live("mouseover", function() {
            $(this).children(".task-buttons").css({'visibility':'visible'});
        });

        $("#tasks>li").live("mouseout", _hide_buttons);

        function _hide_buttons() {
            $(this).children(".task-buttons").css('visibility', 'hidden');
        }

        $(".reveal span").live("click", function() {
            $(this).parent().next(".hidden-note").toggle("normal");
        });

        $(".tasks h3").live("click", _project);

        $("#projects li").live("click", _project);

        $(".project").live("click", _project);

        function _project() {
            request('', 'project', $(this).attr("id"));
        }

        $(".filters span").live("click", function() {
            request('', 'filter', $(this).attr("id"));
        });

        // all the task buttons
        $(".check-done").live("click", function() {
            request('', 'done', $(this).attr("name"));
        });

        $(".action-button").live("click", function() {
            request('', 'action', $(this).attr("name"));
        });

        $("#noaction-button").live("click", function() {
            request('', 'noactions', '');
        });

        $(".archive-button").live("click", function() {
            request('', 'archive', $(this).attr("name"), '', function() {
                show_message(arch_msg);
            });
        });

        $("#archiveall-button").live("click", function() {
            request('', 'archiveall', '', '', function() {
                show_message(all_arch_msg);
            });
        });

        $(".delete-button").live("click", function() {
            request('', 'erase', $(this).attr("name"), '', function() {
                show_message(erase_msg);
            });
        });

        $("#rename-button").live("click", function() {
            var new_name = prompt(rename_msg);
            if (new_name != null && new_name != "") {
                request('', 'rename', new_name);
            }
        });

        $("#remove-button").live("click", function() {
            var result = confirm(remove_msg);
            if (result === true) {
                request('', 'remove', '');
            }
        });

        $(".tab li").live("click", function() {
            var draft = '';
            var tab = $(this).attr("name");
            // pass the draft text state if the edit area is visible
            if (edit_view.css("display") != "none") {
                draft = text_area.val();
            }
            // the '+' tab (add new tab) is called __new__ behind the scenes...
            if (tab == '__new__') {
                var new_name = '';
                new_name = prompt(create_msg);
                if (new_name != null && new_name != "") {
                    request('', 'newtab', new_name, draft, function() {
                        text_area.focus();
                    });
                }
            } else {
                $(".tab li").removeClass("active");
                $(this).addClass("active");
                request('', 'changetab', tab, draft);
            }
        });

        // editable attr only added when user clicks
        $("li.editable").live("click", function() {
            var that = $(this);
            var body = $('body');
            var text = that.attr("name");
            var rows = text.count("\n");
            rows = (rows > 1) ? rows : 2;
            if (body.data('editable') === null) {
                that.editable(function(value) {
                    request('', 'editable', that.attr("id") + ":" + value, '', function() {
                        body.data('editable', null);
                        show_message(edit_msg);
                    });
                },
                {
                    type: 'textarea',
                    /*tooltip: editable_tip,*/
                    indicator: 'css/img/indicator.gif',
                    event: 'dblclick',
                    onblur: 'ignore',
                    cssclass: 'editable-box',
                    cols:40,
                    rows:rows + 1,
                    submit: '<img class="top" src="icons/save.png" width="32" title="' + save_tip + '">',
                    cancel: '<img class="bottom" src="icons/repeat.png" width="32" title="' + cancel_tip + '">',
                    data: function() {
                        _hide_buttons();
                        body.data('editable', that);
                        return text;
                    },
                    onedit: function() {
                        that.find('form textarea').bind('keydown', 'ctrl+return meta+return', function() {
                            that.find('form').trigger('submit');
                        });
                    },
                    onreset: function() {
                        body.data('editable', null);
                    }
                });
            }
        });



        $.address.externalChange(function(e) {
            var address = e.pathNames;
            var tab = address[0];
            // ignore page load events
            if (index_load.val() == 'false' && tab !== undefined)  {
                var event = address[1] === undefined ? '' : address[1];
                var value = address[2] === undefined ? '' : address[2];
                request(tab, event, value);  // has tab => external request
            }

        });

        // replace text in edited page
        $("#replace-button").live("click", function() {
            var find_text = $("#find-word").val();
            var replace_text = $("#replace-word").val();
            if(find_text != "" && replace_text != "") {
                find_text = new RegExp(find_text, "gi");
                var edit_text = text_area.val();
                edit_text = edit_text.replace(find_text, replace_text);
                text_area.val(edit_text);
            }
        });

    }

    /*
     * Main starting point
     */
    $(document).ready(function() {
        init();
        edit_view.hide();
        task_view.show();
        add_events();
        index_load.val('false');
    });

    /*
     * prepare and make the PHP ajax request
     * you can provide a callback that will be called if request was successful:
     * e.g. for clean-up or result messages
     */
    function request(tab, event, value, draft, callback) {
        // close any editable boxes that might be still open
        var body = $('body');
        if (event !== 'editable' && body.data('editable') !== null) {
            body.data('editable').editable('destroy');
            body.data('editable', null);
        }

        // optional args
        tab = tab || '';
        value = value || '';
        draft = draft || '';
        callback = callback || false;

        $("#indicator").show();
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
    function render(response) {
        $("#indicator").hide()
        if (response == '__action__') return true;
        if (response !== undefined && response !== null && response !== '__failed__') {
            update_view(response);
            if (response.address != '' && $.address.value() != response.address) {
                $.address.value(response.address);
            }
            return true;
        } else {
            return false;
        }
    }
    /*
     * refreshes various parts of the view based on JSON data
     * returned to render function
     */
    function update_view(response) {
        if (response.text !== undefined) {
            task_view.hide();
            edit_view.show();
            text_area.val(response.text);
        } else {
            edit_view.hide();
            task_view.show();
        }
        update_elements(response);
    }
    function update_elements(response) {
        if (response.tasks !== undefined) {
            task_view.html(response.tasks);
            // 'sortable' needs to be added each time the task list is updated!
            // and only if in 'all' or 'project' view mode (a filtered list cannot be sorted)
            if (response.event == 'all' || response.event == 'project') {
                $("#tasks").sortable({
                    update: function(event, ui) {
                        var order = $(this).sortable('toArray').toString();
                        request('', 'sort', order);
                    }
                });
            }
        }
        if (response.projects !== undefined) {
            $("#projects").html(response.projects);
        }
        if (response.tags !== undefined) {
            $("#tags").html(response.tags);
        }
        if (response.tabs !== undefined) {
            $("#tabs").html(response.tabs);
        }
    }

    function show_message(message) {
        $("#message-banner span").text(message[0]);
        var colour = message[1];
        var message_banner = $("#message-banner");
        var new_top = $(window).scrollTop() - 15 + "px";
        var left = $(window).width() / 2 - 100 + 'px';
        message_banner.css({"background" : colour, "top" : new_top, "left" : left});
        message_banner.animate({top:"+=20px", opacity:200}, {duration:900});
        message_banner.animate({top:"-=20px", opacity:0}, {duration:600});
    }

    String.prototype.count = function(delim) {
        return this.split(delim).length-1;
    }


})();