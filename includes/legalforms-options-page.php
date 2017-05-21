<form method="post">
    <h2><?php get_admin_page_title();?></h2>
    <div class="soeasy" id="legalform-scripts">
        <form name="<?php echo LF;?>" method="post" action="">
        <table class="form-table">
            <tbody> 
                <tr>
                    <td scope="row">
                        <?php _e('Please enter LegalForms url to get form reference', LF) ?>:
                    </td>
                    <td>
                        <input class="regular-text" type="text" value="<?php echo $this->config['legalforms'];?>" name="legalforms" />
                    </td>
                </tr>
                <tr>
                    <td scope="row">
                        <?php _e('Please enter LegalDocx server url', LF) ?>:
                    </td>
                    <td>
                        <input class="regular-text" type="text" value="<?php echo $this->config['legaldocx'];?>" name="legaldocx" />
                    </td>
                </tr>
                <tr>
                    <td scope="row">
                        <?php _e('Please enter LegalDocx Api Key', LF) ?>:
                    </td>
                    <td>
                        <input class="regular-text" type="text" value="<?php echo $this->config['apiKey'];?>" name="apiKey" />
                    </td>
                </tr>
                <tr>
                    <td scope="row">
                        <?php _e('Load jQuery from CDN on form page', LF) ?>:
                    </td>
                    <td>
                        <label><?php _e('yes');?>
                            <input class="regular-text" <?php echo $this->config['useJQuery'] ? 'checked="checked"' : '';?> type="radio" value="1" name="useJQuery" />
                        </label>
                        <label><?php _e('no');?>
                            <input class="regular-text" <?php echo $this->config['useJQuery'] ? '' : 'checked="checked"';?> type="radio" value="0" name="useJQuery" />
                        </label>
                    </td>
                </tr>
                <tr>
                    <td scope="row">
                        <?php _e('Load bootstrap from CDN on form page', LF) ?>:
                    </td>
                    <td>
                        <label><?php _e('yes');?>
                            <input class="regular-text" <?php echo $this->config['useBootstrap'] ? 'checked="checked"' : '';?> type="radio" value="1" name="useBootstrap" />
                        </label>
                        <label><?php _e('no');?>
                            <input class="regular-text" <?php echo $this->config['useBootstrap'] ? '' : 'checked="checked"';?> type="radio" value="0" name="useBootstrap" />
                        </label>
                    </td>
                </tr>
                <tr>
                    <td scope="row">
                        <?php _e('Load selectize from CDN on form page', LF) ?>:
                    </td>
                    <td>
                        <label><?php _e('yes');?>
                            <input class="regular-text" <?php echo $this->config['useSelectize'] ? 'checked="checked"' : '';?> type="radio" value="1" name="useSelectize" />
                        </label>
                        <label><?php _e('no');?>
                            <input class="regular-text" <?php echo $this->config['useSelectize'] ? '' : 'checked="checked"';?> type="radio" value="0" name="useSelectize" />
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>   
    </div>
    <?php submit_button(); ?>
</form>