/**
 * Main starting point
 */
$(document).ready(function () {

    "use strict";

    app.init();
    app.add_events();
});



var app = (function () {
   
    "use strict";
    
    // public methods
    var pub = {};               
    
    // private methods and vars
    var $tabs,
        $login_form,
        $register_form,
        $login_msg,
        $login_err,
        ajax_file = 'index.php';
        
        
    
    pub.init = function () {
        $tabs           = $('.tabs');
        $login_form     = $('form#login');
        $register_form  = $('form#register');
        $login_msg      = $('#login-msg p');
        $login_err      = $('#login-err ul');
    };
    
    pub.add_events = function() {
        
        $tabs.on("click", "li", function (e) {
            e.preventDefault();
            var $tab = $(this);
            if ( ! $tab.hasClass('selected')) {
                $tabs.children('li').removeClass('selected');
                $(this).addClass('selected');
                $("form").toggle(400);
            }
            var tab = $tab.attr('id');
            $('form#' + tab + ' input.username').focus();
            return false;
        });
        
        $("#forgot-password").on("click", function() {
            alert('Contact your administrator to reset your password');
        });
        
        $login_form.on("submit", function(e) {
            e.preventDefault();
            ajax('login-form', $(this), function(data) {
                if (data.success === true) {
                    window.setTimeout("window.location.reload()", 500);
                } else {
                    activate(data.tab);
                    show_msgs(data.login_msg, data.login_err);
                }
            });
        });
        
        $register_form.on("submit", function(e) {
            e.preventDefault();
            ajax('register-form', $(this), function(data) {
                activate(data.tab);
                show_msgs(data.login_msg, data.login_err);
            });
        });
        
        var activate = function(tab) {
            var $tab = $tabs.children("li#" + tab);
            $tab.trigger("click");
        };
        
        
        var ajax = function(name, $form, responder) {
            $.post(
                ajax_file,
                $form.serialize() + '&login&' + name,
                responder,
                'json'
            );
        };
        
        var show_msgs = function(msg, err) {
            $login_msg.html(msg);
            $login_err.html(err);
        };
    };
    
    // return only public methods
    return pub;
    
}());