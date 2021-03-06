var decodeEntities = (function() {
    // this prevents any overhead from creating the object each time
    var element = document.createElement('div');

    function decodeHTMLEntities (str) {
        if(str && typeof str === 'string') {
            // strip script/html tags
            str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
            str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
            element.innerHTML = str;
            str = element.textContent;
            element.textContent = '';
        }

        return str;
    }

    return decodeHTMLEntities;
})();

(function($) {
    $.fn.datetimepicker.defaults.icons = {
        time: 'fa fa-clock-o',
        date: 'fa fa-calendar',
        up: 'fa fa-chevron-up',
        down: 'fa fa-chevron-down',
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-dot-circle-o',
        clear: 'fa fa-trash',
        close: 'fa fa-times'
    };
})(jQuery);

(function($) {
    $('#legalforms-name').html(legalforms.name);

    var loading = false;

    var builder = new LegalForm();

    var template = builder.build(legalforms.definition);
    var options = builder.calc(legalforms.definition);

    var ractive = new RactiveLegalForm({
        el: $('#doc-wizard').get(0),
        template: template,
        validation: new LegalFormValidation(),
        defaults: options.defaults,
        computed: options.computed,
        meta: options.meta,
        locale: 'nl',
        resolveInstanceMembers: false //prevent autocreation of `data` value, containing all ractive values
    });

    var helptext = builder.buildHelpText(legalforms.definition);
    helptext = decodeEntities(helptext);

    new Ractive({
        el: $('#doc-help')[0],
        template: helptext
    });

    if (legalforms.material !== 'false') {
        $('#doc-wizard').toMaterial();
        $('#doc-wizard').bootstrapMaterialDesign({ autofill: false });
        $('#doc-wizard .btn').addClass('btn-raised').removeClass('btn-outline').removeClass('btn-rounded');
        $('.progress').remove();
        $('#doc').remove();
    } else {
        $('#doc-wizard .btn-default').addClass('btn-secondary');
    }

    
    if (legalforms.standard_login && !legalforms.ask_email) {
        var doneText = $('.wizards-actions button[data-step=done]').html();
    } else {
        var doneText = $('.wizards-actions button[data-step=next]').html();
    }
    $('.wizards-actions button[data-step=done]').html(doneText + '<div class="loader hidden d-none"></div>');

    window.ractive = ractive;

    var storedValues = localStorage.getItem('values');
    if (storedValues) {
        storedValues = JSON.parse(storedValues);
        setTimeout(useSaved, 2000);
    }

    function useSaved() {
        $('#doc-saved-modal').modal({
            backdrop: true
        });

        $('#doc-saved-continue').on('click', function() {
            ractive.set(storedValues);

            /* Manually set all selectize fields */
            var els = jQuery('select.selectized');
            for (var i = 0; i < els.length; i++) {
                var id = els.eq(i).attr('id').replace('field:', '');
                var val = storedValues[id.split('.')[0]][id.split('.')[1]];
                els.eq(i).selectize()[0].selectize.setValue(val);
            }
            $('.form-control').each(function () {
                if ($(this).val()) {
                    $(this).parents('.form-group').removeClass('is-empty');
                }
            })
        })

        $('#doc-saved-discard').on('click', function() {
            localStorage.removeItem('values');
        })
    }

    function getValues() {
        ractive.refreshListItems('remove');
        var values = ractive.get();
        
        var amountInputs = findAmountKeys(values, values['meta']);
        amountInputs.forEach(input => {
            values[input] = values[input] = values[input]['amount'] + ' ' + values[input]['unit'];
        });
        
        for (const key of Object.keys(values)) {
            if (key !== 'meta' && typeof values[key] === 'object') {
                amountInputs = findAmountKeys(values[key], values['meta'][key]);
                amountInputs.forEach(input => {
                    values[key][input] = values[key][input] = values[key][input]['amount'] + ' ' + values[key][input]['unit'];
                });
            }
        }
        
        delete values.$;
        delete values.today;
        delete values.vandaag;
        delete values.meta;
        for (var key in values) {
            if (key.indexOf('\\') > -1 || key.indexOf('-conditions') > -1 ||
                key.indexOf('-expression') > -1 || key.indexOf('-default') > -1) {
                delete values[key];
            }
        }
        return values;
    }
    
    function findAmountKeys(object, meta) {
        return Object.keys(object).filter(key => typeof meta[key] === 'object' && meta[key].type  === 'amount');
    }
    
    function getHeaderHeight() {
      var headerHight = $('header').outerHeight(true);
      if (!headerHight) {
          headerHight = $('div[class*=nav]').filter(function() {
              return $(this).css('position') == 'fixed';
          }).outerHeight(true);
      }
      return headerHight;
    }

    function sendToFlow(account, register) {
        if (loading) return;
        loading = true;

        $('.loader').removeClass('hidden d-none');

        delete legalforms.definition;

        $.ajax({
            url: legalforms.ajaxurl,
            type: 'post',
            data: {
                action: 'process_legalform',
                account: account,
                legalforms: legalforms,
                values: getValues(),
                register: register
            }
        }).done(function(url) {
            window.top.location.href = url;
        }).fail(function(xhr, textStatus) {
            loading = false;
            $('.loader').addClass('hidden d-none');

            if (xhr.status === 409) {
                $('#doc-email-exists').css('display', 'block');
            } else if (xhr.status === 404 || xhr.status === 403 || xhr.status === 401) {
                $('#doc-email-error').css('display', 'block');
            } else {
                $('#doc-error').css('display', 'block');
            }

            updateProgress();
        });
    }

    function sendForgotPassword(email) {
        delete legalforms.definition;

        $.ajax({
            url: legalforms.ajaxurl,
            type: 'post',
            data: {
                action: 'forgot_password',
                email: email,
                legalforms: legalforms
            }
        }).done(function() {
                $('#doc-email-send').css('display', 'block');
        }).fail(function(xhr, textStatus) {
            if (xhr.status === 400) {
                $('#doc-email-error').css('display', 'block');
            } else {
                $('#doc-error').css('display', 'block');
            }
        });
    }

    function updateProgress() {
        var totalSections = jQuery('#legalforms-plugin #doc-wizard .wizard-step').length;
        if (legalforms.standard_login !== 'true' ||
                (legalforms.standard_login === 'true' && legalforms.ask_email === 'true')) {
            totalSections += 1;
        }

        $('#legalforms-plugin .progress-bar').css('opacity', 1);

        if (loading) {
            var currentNumber = totalSections;
        }
        else if ($('#legalforms-plugin #doc-wizard-login .wizard-step.active, \
               #legalforms-plugin #doc-wizard-register .wizard-step.active, \
               #legalforms-plugin #doc-wizard-email .wizard-step.active').length) {
            var currentNumber = totalSections - 1;
        } else {
           var currentSection = $('#legalforms-plugin #doc-wizard .wizard-step.active');
           var currentNumber = Math.max($('#legalforms-plugin #doc-wizard .wizard-step').index(currentSection), 0);
        }

        $('#legalforms-plugin .progress-bar').text(Math.round(currentNumber / totalSections * 100) + '%');
        $('#legalforms-plugin .progress-bar').width(currentNumber / totalSections * 100 + '%');
    }

    $(document).on('click', '.wizards-actions button[data-step="done"]', function() {
        if (!$('#doc-wizard form').get().every(function (form) {
            return form.checkValidity();
        })) {
            return;
        }

        if (legalforms.standard_login === 'true' && legalforms.ask_email === 'true') {
            $('#doc-wizard').hide();
            $('#doc-wizard-actions').hide();
            $('#doc-wizard .wizard-step').removeClass('active');
            $('#doc-wizard-email .wizard-step').addClass('active');
            $('#doc-wizard-email').show();
            $('#doc-wizard-actions button[data-step="done"]').removeClass('in');
            $('html, body').animate({
                scrollTop: $('#legalforms-plugin').offset().top - getHeaderHeight() - 10
            }, 500);
        } else if (legalforms.standard_login === 'true') {
            sendToFlow({}, false);
        } else {
            $('#doc-wizard').hide();
            $('#doc-wizard-actions').hide();
            $('#doc-wizard .wizard-step').removeClass('active');
            $('#doc-wizard-register .wizard-step').addClass('active');
            $('#doc-wizard-register').show();
            $('#doc-wizard-actions button[data-step="done"]').removeClass('in');
            $('html, body').animate({
                scrollTop: $('#legalforms-plugin').offset().top - getHeaderHeight() - 10
            }, 500);
        }
        
        $('#doc').hide();

        updateProgress();
    });

    $(document).on('click', '#switch-login', function() {
        $('#doc-wizard-register').hide();
        $('#doc-wizard-register .wizard-step').removeClass('active');
        $('#doc-wizard-login .wizard-step').addClass('active');
        $('#legalforms-plugin .alert.alert-danger').css('display', 'none');
        $('#doc-wizard-login').show();
    });

    $(document).on('click', '#switch-forgot', function() {
        $('#doc-wizard-login').hide();
        $('#doc-wizard-login .wizard-step').removeClass('active');
        $('#doc-wizard-forgot .wizard-step').addClass('active');
        $('#legalforms-plugin .alert.alert-danger').css('display', 'none');
        $('#doc-wizard-forgot').show();
    });

    $(document).on('click', '#doc-wizard-register button[data-step="register"]', function() {
        if (!document.getElementById('form-register').checkValidity()) return;

        var account = {
            name: $('#doc-wizard-register [name="account.name"]').val(),
            email: $('#doc-wizard-register [name="account.email"]').val(),
            password: $('#doc-wizard-register [name="account.password"]').val(),
        }
        sendToFlow(account, true);
        updateProgress();
    });

    $(document).on('click', '#doc-wizard-login button[data-step="login"]', function() {
        if (!document.getElementById('form-login').checkValidity()) return;

        var account = {
            email: $('#doc-wizard-login [name="account.email"]').val(),
            password: $('#doc-wizard-login [name="account.password"]').val(),
        }
        sendToFlow(account, false);
        updateProgress();
    });

    $(document).on('click', '#doc-wizard-email button[data-step="done"]', function() {
        if (!document.getElementById('form-email').checkValidity()) return;

        var account = {
            user_email: $('#doc-wizard-email [name="account.user_email"]').val(),
            user_name: $('#doc-wizard-email [name="account.user_name"]').val()
        }
        sendToFlow(account, false);
        updateProgress();
    });

    $(document).on('click', '#doc-wizard-login button[data-step="previous"], \
            #doc-wizard-register button[data-step="previous"]', function() {
        $('#doc-wizard-register').hide();
        $('#doc-wizard-login').hide();
        $('#doc-wizard-register .wizard-step').removeClass('active');
        $('#doc-wizard-login .wizard-step').removeClass('active');
        $('#doc-wizard .wizard-step:last').addClass('active');
        $('#doc-wizard').show();
        $('#doc-wizard-actions').show();
        $('#doc').show();
        $('#doc-wizard-actions button[data-step="done"]').addClass('in');
        updateProgress();
    })

    $(document).on('click', '#doc-wizard-forgot button[data-step="forgot"]', function() {
        if (!document.getElementById('form-forgot').checkValidity()) return;

        sendForgotPassword($('#doc-wizard-forgot [name="account.email"]').val());

        $('#doc-wizard-forgot').hide();
        $('#doc-wizard-forgot .wizard-step').removeClass('active');
        $('#doc-wizard-login .wizard-step').addClass('active');
        $('#doc-wizard-login').show();
    });

    $(document).on('click', '.doc-save', function() {
        var values = getValues();
        localStorage.setItem('values', JSON.stringify(values));
        if (!jQuery('#doc-save-alert').length) {
            $('#doc-wizard .wizard-step.active .wizards-actions').after([
                '<div class="clearfix"></div>',
                '<div class="alert alert-info" id="doc-save-alert">',
                '    Voortgang succesvol opgeslagen',
                '</div>'
            ].join(''));
            setTimeout(function () {
                $('#doc-save-alert').fadeOut('normal', function() {
                    $(this).remove();
                });
            }, (5000));
        }
    });

    $(document).on('click', 'button[data-step=next], button[data-step=prev]', function() {
        updateProgress();
        if (legalforms.material !== 'false') {
            $('html, body').animate({
                scrollTop: $('.wizard-step.active').offset().top - getHeaderHeight() - 10
            }, 500);
        } else {
            $('html, body').animate({
                scrollTop: $('#legalforms-plugin').offset().top - getHeaderHeight() - 10
            }, 500);
        }
    });

    $(document).on('keyup', function(e) {
        if (e.which == 13) {
            jQuery('#doc-wizard-actions button[data-step="next"].in, \
                    #doc-wizard-actions button[data-step="done"].in, \
                    #doc-wizard-register .wizard-step.active .wizards-actions button[data-step="register"], \
                    #doc-wizard-login .wizard-step.active .wizards-actions button[data-step="login"], \
                    #doc-wizard-email .wizard-step.active .wizards-actions button[data-step="done"], \
                    #doc-wizard-forgot .wizard-step.active .wizards-actions button[data-step="forgot"]').click();
        }
        return false;
    })
    
    $(document).on('click', '#doc-wizard-register .password-toggle', function() {
        let input = $('#doc-wizard-register input[name="account.password"]');
        let eye = $('#doc-wizard-register .password-toggle i');
        if (input.prop('type') === 'password') {
            input.prop('type', 'text');
            eye.removeClass('fa-eye');
            eye.addClass('fa-eye-slash')
        } else {
            input.prop('type', 'password');
            eye.removeClass('fa-eye-slash');
            eye.addClass('fa-eye')
        }
    });
})(jQuery);
