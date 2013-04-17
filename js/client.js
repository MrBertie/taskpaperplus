/**
 * Main starting point
 */
$(document).ready(function () {

    "use strict";

    app.init();
    app.show_view();
    app.add_events();
    app.make_sortable();
    app.loaded();
});



var app = (function () {

    "use strict";


    var pub = {};               // all public methods


    var ajax_file = '',         // name of php ajax target file
        $view_tasks,
        $edit_tasks,
        $text_area,
        $body,
        $index_load,
        $search_box,
        lang            = {},   // language strings for app use
        task_button_tpl = '',   // template for task line buttons
        task_prefix     = '',
        page_address    = '',   // current (deep link) page address
        restricted      = false;    // restricd = trash or archive


    pub.init = function () {

        ajax_file       = "index.php";
        $view_tasks     = $("#view-tasks");
        $edit_tasks     = $("#edit-tasks");
        $text_area      = $("#edit-tasks>textarea");
        $body           = $('body');
        $index_load     = $('#page-load');
        $search_box     = $("#search-box");
        lang            = JSON.parse($("#jslang").html());
        task_button_tpl = $('#task-buttons-tpl').val();
        task_prefix     = $('#task-prefix').val();

        // set initial page address correctly (deep link after the #)
        page_address    = $("#page-address").val();

        // signals whether this was a full page load/rebuild or not
        $.address.value(page_address);
        $body.data('editable', null);

        // allow use of tab key in all textareas, makes it easier to add notes
        $.fn.tabOverride.tabSize(4);
        $(document).tabOverride(true, "textarea");
    };


    pub.loaded = function () {
        $index_load.val('false');
    };


    /**
     * Prepare and make the PHP ajax request
     * you can provide a callback if request was successful:
     * e.g. for clean-up or result messages
     */
    var request = function (data, callback) {

        // set POST based on draft or size of value!
        var has_draft = typeof (data.draft) !== "undefined",
            long_value = typeof (data.value) !== "undefined" && data.value.length > 200,
            post = has_draft || long_value;

        // close any editable boxes that might be still open
        if (data.event !== 'editable' && $body.data('editable') !== null) {
            $body.data('editable').editable('destroy');
            $body.data('editable', null);
        }

        callback = callback || false;

        throbber_on();

        if (post === true) {
            $.post(ajax_file,
                data,
                function (response) {
                    var success = render(response);
                    if (success && callback !== false) {
                        callback();
                    }
                }, 'json');
        } else {
            $.getJSON(ajax_file,
                data,
                function (response) {
                    var success = render(response);
                    if (success && callback !== false) {
                        callback();
                    }
                });
        }
    };


    /**
     * Generic ajax response function
     * renders any returned response data and updates address where necessary
     */
    var render = function (response) {

        throbber_off();

        if (response === '__action__') {
            return true;
        }
        if (response !== undefined &&
                response !== null &&
                response !== '__failed__') {

            restricted = response.restricted;
            update_view(response);

            if (response.address !== '' && $.address.value() !== response.address) {
                $.address.value(response.address);
            }
            return true;
        } else {
            return false;
        }
    };


    pub.show_view = function () {
        $edit_tasks.hide();
        $view_tasks.show();
    };

    pub.make_sortable = function () {
        // 'sortable' function needs to be added on each refresh
        $view_tasks.children("#sortable").sortable({
            update: function (event, ui) {
                var order = $(this).sortable('toArray');
                request({event: 'sort', value: order});
            }
        });
    };

    /*
     * refreshes various parts of the view based on JSON data
     * returned to render function
     */
    var update_view = function (response) {

        // show edit area if necessary
        if (response.event === 'edit') {
            $view_tasks.hide();
            $edit_tasks.show();
            $text_area.val(response.text);
        } else {
            pub.show_view();
        }

        // update the page content where necessary

        if (response.tasks !== undefined) {
            $view_tasks.html(response.tasks);
            pub.make_sortable();
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
        if (response.tabtools !== undefined) {
            $("#tabtools").html(response.tabtools);
        }
        if (restricted === true) {
            $(".restrict").prop("disabled", true);
        } else {
            $(".restrict").prop("disabled", false);
        }
    };


    var show_message = function (message) {

        var text = message[0],
            colour = message[1],
            new_top = ($(window).scrollTop() - 20) + "px",
            left = $(window).width() / 2 - 100 + 'px',
            $message_banner = $("#message-banner");

        $("#message-banner span").text(text);

        $message_banner
            .css({"background" : colour, "top" : new_top, "left" : left})
            .animate({top: "+=20px", opacity: 200}, {duration: 900})
            .animate({opacity: 0}, {duration: 900});
    };


    var throbber_on = function () {
        $("#indicator").show();
    };

    var throbber_off = function () {
        $("#indicator").hide();
    };


    String.prototype.count = function (delim) {
        return this.split(delim).length - 1;
    };


    pub.add_events = function () {

        $("#home").on("click", reset);

        $("#purge-session").on("click", function () {
            request({event: 'purgesession'}, function() {
                show_message(['Session cleared! Reloading...', lang.green]);
                window.setTimeout("window.location.reload()", 1500);
                $index_load.val('false');
            });
        });

        $("#purge-cache").on("click", function () {
            request({event: 'purgecache'}, function() {
                show_message(['Cache cleared!', lang.yellow]);
            });
        });

        $('#footer select').on('change', function () {
            request({event: 'lang', value: this.value}, function () {
                show_message([lang.lang_change_msg, lang.green]);
                window.setTimeout("window.location.reload()", 1000);
            });
        });


        // Search Box

        var show_reset_search = function () {
            $search_box.data('can_reset', true);
            $("#reset-search").show();
        };

        var reset_search = function () {
            if ($search_box.data('can_reset') === true) {
                $search_box.val('');
                $search_box.trigger('blur');
            }
        };

        $("#reset-search").on("click", function () {
            reset_search();
        });


        var add_task = function () {
            var expression = $search_box.val();
            if (expression !== '') {
                // add the task prefix if missing
                if (expression.charAt(0) !== task_prefix) {
                    expression = task_prefix + " " + expression;
                }
                // new task to be added
                request({event: 'add', value: expression}, function () {
                    show_message(lang.add_msg);
                    $search_box.val('').attr('rows', '1');
                });
            } else {
                reset_search();
            }
        };

        var do_search = function () {
            var expression = $search_box.val();
            if (expression !== "") {
                request({event: 'search', value: expression});
            // enter in a blank box == reset (common practice)
            } else {
                request({event: 'all'});
            }
        };

        $search_box
            .bind('keydown', 'ctrl+return meta+return', add_task)
            .bind('keydown', 'return', function () {
                var val = $(this).val();
                // check for a task entry (always "- " at beginning) and allow returns
                if (val.substr(0, 2) === (task_prefix + " ")) {
                    return;
                // ignore empty returns
                } else if (val === '') {
                    reset_search();
                } else {
                    do_search();
                }
            })
            // increase box size if this is a task entry
            .on("keyup", function () {
                if ($(this).val() === task_prefix) {
                    this.rows = 3;
                }
            })
            .on("click", function () {
                show_reset_search();
            })
            .on("focus", function () {
                show_reset_search();
            })
            .on("blur", function () {
                if ($(this).val() === '') {
                    $("#reset-search").hide();
                    $search_box.data('can_reset', false);
                }
                this.rows = 1;
            });


        // Tab toolbar

        var save_edits = function () {
            request({event: 'save', value: $text_area.val()});
            $('#edit-view textarea')
                .unbind('keydown', 'ctrl+return meta+return', save_edits)
                .unbind('keydown', 'esc', reset);
        };
        $("#edit-button").on("click", function () {
            request({event: 'edit'}, function () {
                $('#edit-tasks textarea')
                    .bind('keydown', 'ctrl+return meta+return', save_edits)
                    .bind('keydown', 'esc', reset);
            });
        });
        $("#remove-actions-button").on("click", function () {
            request({event: 'remove_actions'});
        });
        $("#archive-done-button").on("click", function () {
            request({event: 'archive_done'}, function () {
                show_message(lang.arch_done_msg);
            });
        });
        $("#trash-done-button").on("click", function () {
            request({event: 'trash_done'}, function () {
                show_message(lang.trash_done_msg);
            });
        });
        $("#rename-button").on("click", function () {
            var new_name = prompt(lang.rename_msg);
            if (new_name !== null && new_name !== "") {
                request({event: 'rename', value: new_name});
            }
        });
        $("#remove-button").on("click", function () {
            var result = confirm(lang.remove_msg);
            if (result === true) {
                request({event: 'remove'});
            }
        });


        // Inside Text Edit box

        var reset = function () {
            request({event: 'all'});
        };

        $("#edit-tasks input.cancel-button").on("click", reset);

        /* this is the 'Save' button, for editing area */
        $("#edit-tasks input.save-button").on("click", save_edits);

        // replace text in edited page
        $("#edit-tasks").on("click", "#replace-button", function () {
            var find_text = $("#find-word").val();
            var replace_text = $("#replace-word").val();
            if(find_text !== "" && replace_text !== "") {
                find_text = new RegExp(find_text, "gi");
                var edit_text = $text_area.val();
                edit_text = edit_text.replace(find_text, replace_text);
                $text_area.val(edit_text);
            }
        });


        // Tab switching

        $("#tabs").on("click", "li", function (e) {
            e.preventDefault();
            var draft = '',
                tab = $(this).attr("name");

            // pass the draft text state if the edit area is visible
            if ($edit_tasks.css("display") !== "none") {
                draft = $text_area.val();
            }
            // the '+' tab (add new tab) is called __new__ behind the scenes...
            if (tab === '__new__') {
                var new_name = '';
                new_name = prompt(lang.create_msg);
                if (new_name !== null && new_name !== "") {
                    request({event: 'show', tab: new_name, draft: draft}, function () {
                        $text_area.focus();
                    });
                }
            } else {
                request({event: 'show', tab: tab, draft: draft});
            }
        });



        var show_project = function (target) {
            request({event: 'project', value: $(target).attr("data-index")});
        };

        $(".projects").on("click", "li", function () {
            show_project(this);
        });

        var show_tag = function (target) {
            request({event: 'tag', value: target.innerHTML});
        };

        // filter list in meta column
        $(".meta")
            .on("click", ".filters li span", function () {
                request({event: 'filter', value: $(this).attr("id")});
            })
            .on("click", "li .tag", function () {
                show_tag(this);
            });


        var hide_task_button_tpl = function (target) {
            $(target).children(".task-buttons").remove();
        };


        // all events in the main task list area

        $("#view-tasks")

            // add or remove the task buttons
            .on("mouseenter", "li.task", function () {
                var tpl = task_button_tpl.replace(/\{id\}/gm, $(this).attr("id"));
                $(tpl).appendTo(this);
            })
            .on("mouseleave", "li.task", function () {
                hide_task_button_tpl(this);
            })


            // all the task buttons
            .on("click", "li .check-done", function () {
                request({event: 'done', value: $(this).attr("id")});
            })
            .on("click", "li .action-button", function () {
                request({event: 'action', value: $(this).attr("id")});
            })
            .on("click", "li .archive-button", function () {
                request({event: 'archive', value: $(this).attr("id")}, function () {
                    show_message(lang.arch_msg);
                });
            })
            .on("click", "li .trash-button", function () {
                request({event: 'trash', value: $(this).attr("id")}, function () {
                    show_message(lang.trash_msg);
                });
            })


            // tags
            .on("click", ".tag, .date-tag", function () {
                show_tag(this);
            })


            // show or hide notes
            .on("click", ".reveal", function () {
                $(this).hide().next().show();
            })
            .on("click", ".hidden-note", function () {
                $(this).prev().show();
                $(this).hide();
            })


            // show a project
            .on("click", "li.project p, li .project", function () {
                show_project(this);
            })


            // editable attr only added when user clicks
            .on("click", "li.editable", function () {
                var that = $(this),
                    text = that.attr("name"),
                    rows = text.count("\n");

                rows = (rows > 1) ? rows : 2;

                if ($body.data('editable') === null) {
                    that.editable(function (value) {
                        request({event: 'editable', key: that.attr("id"), value: value}, function () {
                            $body.data('editable', null);
                            show_message(lang.edit_msg);
                        });
                    },
                        {
                            type: 'textarea',
                            /*tooltip: editable_tip,*/
                            indicator: 'css/img/indicator.gif',
                            event: 'dblclick',
                            onblur: 'ignore',
                            cssclass: 'editable-box',
                            cols: 40,
                            rows: rows + 1,
                            submit: '<img class="top" src="images/save.png" title="' + lang.save_tip + '">',
                            cancel: '<img class="bottom" src="images/repeat.png" title="' + lang.cancel_tip + '">',
                            data: function () {
                                hide_task_button_tpl(that);
                                $body.data('editable', that);
                                return text;
                            },
                            onedit: function () {
                                var edit_area = that.find('form textarea');
                                edit_area.bind('keydown', 'ctrl+return meta+return', function () {
                                    that.find('form').trigger('submit');
                                });
                                edit_area[0].selectionStart = edit_area[0].selectionEnd = edit_area[0].value.length;
                            },
                            onreset: function () {
                                $body.data('editable', null);
                            }
                        });
                }
            });



        $.address.externalChange(function (e) {
            var address = e.pathNames,
                tab = address[0];
            // ignore page load events
            if ($index_load.val() === 'false' && tab !== undefined) {
                var state = address[1] === undefined ? '' : address[1];
                var value = address[2] === undefined ? '' : address[2];
                request({event: 'show', tab: tab, state: state, value: value});
            }
        });


        /* TOOLTIPS */

        $("#view-tasks")
            .on("hover", "li.task>p", function () {
                $(this).attr("title", lang.edit_in_place_tip);
            })
            .on("hover", "li textarea", function () {
                $(this).attr("title", lang.editable_tip);
            })
            .on("hover", "li.task ul .reveal", function () {
                $(this).attr("title", lang.reveal_tip);
            });

    };

    // return only public methods
    return pub;

}());