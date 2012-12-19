<?php /* @var $instance DvsSlider */ ?>
<div class="wrap">  
    <div class="icon32" id="icon-options-general"></div>
    <h2><?php echo DvsSlider::PLUGIN_NAME ?></h2>
    <form action="options.php" method="post">  
        <?php settings_fields($instance->settings_group_name); ?>
        <?php do_settings_sections($instance->page_slug); ?>
        <p class="submit">  
            <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', DvsSlider::DOMAIN); ?>" />  
        </p>  
    </form>  
</div>
