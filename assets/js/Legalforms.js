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
        locale: 'en',
        resolveInstanceMembers: false //prevent autocreation of `data` value, containing all ractive values
    });

    var helptext = builder.buildHelpText(legalforms.definition);

    new Ractive({
        el: $('#doc-help-'+legalforms.id)[0],
        template: helptext
    });
    if (legalforms.useMaterial === 'true') {
        $('#doc-wizard').toMaterial();
    }

    window.ractive = ractive;

    $(document).on('click', '#doc-wizard button[data-step="done"]', function() {

        ractive.refreshListItems('remove');
        var values = ractive.get();
        delete values.meta;
        delete values.$;
        delete values.today;
        delete values.vandaag;

        $.ajax({
            url: legalforms.response_url,
            type: 'POST',
            crossDomain: true,
            dataType: "json",
            data: JSON.stringify({
                action: 'legalforms_apply_form',
                form_referense: legalforms.id,  
                response_url: legalforms.response_url,
                data: {
                    values: values,
                    step: 'finished'
                }
            })
        }).done(function(data) {
            console.log('redirect_page is ', legalforms.redirect_page);
            if (legalforms.redirect_page != '') {
                window.location.href = legalforms.redirect_page;
            }

            return;
        }).fail(function(data) {
            console.log('Error on put data to the response_url', legalforms.redirect_page);
        });
    });
})(jQuery);
    