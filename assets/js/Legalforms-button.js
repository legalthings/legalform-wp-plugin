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
                              name: 'reference',
                              id: 'reference',
                              label: 'Put form reference here',
                              multiline: false
                         },
                          {
                              type: 'textbox',
                              name: 'response_url',
                              id: 'response_url',
                              label: 'Type the response url of form here',
                              multiline: false,
                              width: 500
                          },
                          {
                              type: 'textbox',
                              name: 'redirect_page',
                              label: 'Type the redirect page here',
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
                              ed.insertContent( '[legalforms reference="' + e.data.reference
                                 + '" response_url="' + e.data.response_url 
                                 + '"  redirect_page="' + e.data.redirect_page
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