<form method="post">
    <h2><?php get_admin_page_title();?></h2>
    <div class="soeasy" id="legalform-scripts">
        <form name="<?php echo LT_LFP;?>" method="post" action="">
        <table class="form-table">
            <tbody>
                <tr>
                    <td scope="row">
                        <?php _e('Please enter Legalthings base url', LT_LFP) ?>:
                    </td>
                    <td>
                        <input class="regular-text" type="text" value="<?php echo esc_attr($this->config['base_url']);?>" name="base_url" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php submit_button(); ?>
</form>
