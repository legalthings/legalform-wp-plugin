jQuery(document).ready(function($) {
    tinymce.create('tinymce.plugins.legalforms_plugin', {
        init : function(ed, url) {
                // Register command for when button is clicked
                ed.addCommand('legalforms_insert_shortcode', function() {
                    content =  '[shortcode]';

                    tinymce.execCommand('mceInsertContent', false, '[legalforms]');
                });
            // Register buttons - trigger above command when clicked
            ed.addButton('legalforms_button',
               {
                  title : 'Insert Legal Form',
                  image: url + '/../img/legalforms.png',
                  onclick: function (e) {
                      ed.windowManager.open( {
                          title: 'Insert a LegalForm',
                          body: [
                          {
                             type: 'textbox',
                              name: 'template',
                              id: 'template',
                              label: 'Put form template here',
                              multiline: false
                         },
                         {
                              type: 'textbox',
                              name: 'flow',
                              id: 'flow',
                              label: 'Type the legalthings flow here',
                              multiline: false
                          },
                          {
                              type: 'checkbox',
                              name: 'useMaterial',
                              id: 'useMaterial',
                              label: 'Use Material design?',
                              text: 'Yes',
                              }],
                          onsubmit: function( e ) {
                              ed.insertContent( '[legalforms template="' + e.data.template
                                 + '"  flow="' + e.data.flow
                                 + '" material="' + e.data.useMaterial + '"]');
                          }
                      });
                  }
               });
        },
    });

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('legalforms_button', tinymce.plugins.legalforms_plugin);
});
