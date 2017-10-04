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

    function sendToFlow(account) {
        $.ajax({
            url: legalforms.base_url + '/service/iam/sessions',
            type: 'POST',
            crossDomain: true,
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(account)
        }).done(function(session) {
            $.ajax({
                url: legalforms.base_url + '/service/flow/processes',
                type: 'POST',
                crossDomain: true,
                dataType: 'json',
                contentType: 'application/json',
                headers: {'X-Session': session.id},
                data: JSON.stringify({
                  scenario: legalforms.flow,
                  data: {
                      values: getValues(),
                      template: legalforms.template,
                      name: legalforms.name,
                      organization: session.user.employment[0].organization.id
                  }
                })
            }).done(function(data) {
                if (legalforms.done_url != '') {
                    var url = legalforms.done_url;
                } else {
                    var url = legalforms.base_url + '/processes/' + data.id + '?auto_open=true&hash=' + session.id;
                }
                window.top.location.href = url;
            });
        }).fail(function(data) {
            $('#doc-email-error').removeClass('hidden');
        });
    }

    $(document).on('click', '#doc-wizard button[data-step="done"]', function() {
        if (legalforms.standard_login === 'true') {
            var account = {
                email: legalforms.standard_email,
                password: legalforms.standard_password
            }
            sendToFlow(account);
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

    $(document).on('click', '#doc-wizard-register button[data-step="register"]', function() {
        var account = {
            name: $('#doc-wizard-register [name="account.name"]').val(),
            email: $('#doc-wizard-register [name="account.email"]').val(),
            password: $('#doc-wizard-register [name="account.password"]').val(),
        }

        $.ajax({
              url: legalforms.base_url + '/service/iam/users',
              type: 'POST',
              crossDomain: true,
              dataType: 'json',
              contentType: 'application/json',
              data: JSON.stringify(account)
        }).done(function() {
            sendToFlow(account);
        });
    });

    $(document).on('click', '#doc-wizard-login button[data-step="login"]', function() {
        var account = {
            email: $('#doc-wizard-login [name="account.email"]').val(),
            password: $('#doc-wizard-login [name="account.password"]').val(),
        }
        sendToFlow(account);
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
