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
    $('#legalforms-name').html(legalforms.name);

    var loading = false;

    var builder = new LegalForm();

    var template = builder.build(legalforms.definition);
    var options = builder.calc(legalforms.definition);

    var ractive = new RactiveLegalForm({
        el: $('#doc-wizard'),
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
        el: $('#doc-help-'+legalforms.id)[0],
        template: helptext
    });

    if (legalforms.material !== 'false') {
        $('#doc-wizard').toMaterial();
        $('#doc-wizard .btn').addClass('btn-raised').removeClass('btn-outline').removeClass('btn-rounded');
    } else {
        $('#doc-wizard .btn-default').addClass('btn-secondary');
    }

    $('button[data-step=done]').after([
        '<button class="btn btn-default btn-raised doc-save pull-right">',
        'Bewaar voor later',
        '</button>'
    ].join(''));

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
        delete values.$;
        delete values.today;
        delete values.vandaag;
        delete values.meta;
        for (var key in values) {
            if (key.indexOf('\\') > -1) {
                delete values[key];
            }
        }
        return values;
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

        $.ajax({
            url: legalforms.ajaxurl,
            type: 'post',
            data: {
                action: 'process_legalform',
                _wpnonce: legalforms.nonce,
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
                $('#doc-email-exists').removeClass('hidden d-none');
            } else if (xhr.status === 401) {
                $('#doc-email-error').removeClass('hidden d-none');
            } else {
                $('#doc-error').removeClass('hidden d-none');
            }
        });
    }

    function sendForgotPassword(email) {
        $.ajax({
            url: legalforms.ajaxurl,
            type: 'post',
            data: {
                action: 'forgot_password',
                _wpnonce: legalforms.nonce,
                email: email,
                legalforms: legalforms
            }
        }).done(function() {
                $('#doc-email-send').removeClass('hidden d-none');
        }).fail(function(xhr, textStatus) {
            if (xhr.status === 400) {
                $('#doc-email-error').removeClass('hidden d-none');
            } else {
                $('#doc-error').removeClass('hidden d-none');
            }
        });
    }

    $(document).on('click', '#doc-wizard button[data-step="done"]', function() {
        if (legalforms.standard_login === 'true' && legalforms.ask_email === 'true') {
            $('#doc-wizard').hide();
            $('#doc-wizard-email').show();
            $('html, body').animate({
                scrollTop: $('#doc-wizard').siblings('h1').offset().top - getHeaderHeight() - 10
            }, 500);
        } else if (legalforms.standard_login === 'true') {
            var account = {
                email: legalforms.standard_email,
                password: legalforms.standard_password,
            };
            sendToFlow(account, false);
        } else {
            $('#doc-wizard').hide();
            $('#doc-wizard-register').show();
            $('html, body').animate({
                scrollTop: $('#doc-wizard').siblings('h1').offset().top - getHeaderHeight() - 10
            }, 500);
        }
    });

    $(document).on('click', '#switch-login', function() {
        $('#doc-wizard-register').hide();
        $('#doc-wizard-login').show();
    });

    $(document).on('click', '#switch-forgot', function() {
        $('#doc-wizard-login').hide();
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
    });

    $(document).on('click', '#doc-wizard-login button[data-step="login"]', function() {
        if (!document.getElementById('form-login').checkValidity()) return;

        var account = {
            email: $('#doc-wizard-login [name="account.email"]').val(),
            password: $('#doc-wizard-login [name="account.password"]').val(),
        }
        sendToFlow(account, false);
    });

    $(document).on('click', '#doc-wizard-email button[data-step="done"]', function() {
        if (!document.getElementById('form-email').checkValidity()) return;

        var account = {
            email: legalforms.standard_email,
            password: legalforms.standard_password,
            user_email: $('#doc-wizard-email [name="account.user_email"]').val(),
        }
        sendToFlow(account, false);
    });

    $(document).on('click', '#doc-wizard-login button[data-step="previous"], \
            #doc-wizard-register button[data-step="previous"]', function() {
        $('#doc-wizard-register').hide();
        $('#doc-wizard-login').hide();
        $('#doc-wizard').show();
    })

    $(document).on('click', '#doc-wizard-forgot button[data-step="forgot"]', function() {
        if (!document.getElementById('form-forgot').checkValidity()) return;

        sendForgotPassword($('#doc-wizard-forgot [name="account.email"]').val());

        $('#doc-wizard-forgot').hide();
        $('#doc-wizard-login').show();
    });

    $(document).on('click', '.doc-save', function() {
        var values = getValues();
        localStorage.setItem('values', JSON.stringify(values));
        $('#doc-wizard .wizard-step.active .wizards-actions').after([
            '<div class="clearfix"></div>',
            '<div class="alert alert-info" id="doc-save-alert">',
            '    Voortgang succesvol opgeslagen',
            '</div>'
        ].join(''));
        setTimeout(function () {
          $('#doc-save-alert').fadeOut();
      }, (5000));
    });

    $(document).on('click', 'button[data-step=next], button[data-step=prev]', function() {
      $('html, body').animate({
          scrollTop: $('.wizard-step.active').offset().top - getHeaderHeight() - 10
      }, 500);
    });
})(jQuery);
