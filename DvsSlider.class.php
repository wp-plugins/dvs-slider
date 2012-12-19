<?php

class DvsSlider {
    
    public $base_file;
    public $plugin_basename;
    public $post_type;
    public $column_name_caption;
    public $column_name_link;
    public $column_name_thumb;
    public $column_name_order;
    public $meta_name_caption;
    public $meta_name_link;
    public $meta_name_order;
    public $settings_group_name;
    public $page_slug;
    public $settings;
    public $settings_section_basename;
    
    public static $instance = null;
    
    const DOMAIN = 'dvs_slider';
    const NONCE = 'dvs_nonce';
    const PLUGIN_NAME = 'DVS Slider';
    
    public static function getInstance($base_file = null) {
        
        if (!self::$instance) {
            self::$instance = new DvsSlider($base_file);
        }
        
        return self::$instance;
    }
    
    private function __construct($base_file) {
        
        $this->base_file = $base_file;
        $this->plugin_basename = plugin_basename($this->base_file);
        $this->post_type = 'dvs_slider_image';
        
        $this->column_name_caption = self::DOMAIN."_caption";
        $this->column_name_link = self::DOMAIN."_link";
        $this->column_name_thumb = self::DOMAIN."_thumb";
        $this->column_name_order = self::DOMAIN."_order";
        
        $this->meta_name_caption = self::DOMAIN."_caption";
        $this->meta_name_link = self::DOMAIN."_link";
        $this->meta_name_order = self::DOMAIN."_order";
        
        $this->settings_group_name = self::DOMAIN."_settings";
        $this->settings_section_basename = self::DOMAIN."_settings_section";
        $this->page_slug = self::DOMAIN;
        $this->settings = array(
            'main' => array(
                'title' => __('Main Settings', self::DOMAIN),
                'description_cb' => array($this, 'echoSettingsDescription'),
                'fields' => array(
                    'image_size_name' => array(
                        'title' => __('Image Size Name', self::DOMAIN),
                        'html_cb' => array($this, 'echoSettingImageSizeName'),
                    ),
                ),
            ),
        );
        
        add_action('init', array($this, 'init'));
        add_action('save_post', array($this, 'save_post'));
        
        /* Only run our customization on the 'edit.php' page in the admin. */
        add_action('load-edit.php', array($this, 'load_edit_php'));
        
        // [dvs_slider]
        add_shortcode(self::DOMAIN, array($this, 'getSliderHtml'));
        
        add_filter(
            "manage_edit-{$this->post_type}_sortable_columns", 
            array($this, 'manage_edit_sortable_columns')
        );
        add_filter(
            "manage_{$this->post_type}_posts_columns", 
            array($this, 'manage_posts_columns'), 
            10
        );
        add_action(
            "manage_{$this->post_type}_posts_custom_column", 
            array($this, 'manage_posts_custom_column'), 
            10, 
            2
        );
            
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    public function admin_menu() {
        
        add_options_page(self::PLUGIN_NAME, self::PLUGIN_NAME, 'manage_options', $this->page_slug, array($this, 'add_options_page'));
    }
    
    public function add_options_page() {
        
        echo self::getPartial('options', array(
            'instance' => $this,
        ));
    }
    
    public function admin_init(){
        register_setting(
            $this->settings_group_name, 
            $this->settings_group_name, 
            array($this, 'validateSettings')
        );
        
        foreach ($this->settings as $section_name => $section_details) {
            
            $section_id = $this->settings_section_basename."_".$section_name;
            
            add_settings_section(
                $section_id,
                $section_details['title'], 
                $section_details['description_cb'],
                $this->page_slug
            );
            
            foreach ($section_details['fields'] as $field_name => $field_details) {
                
                add_settings_field(
                    $this->settings_group_name."_".$field_name,
                    $field_details['title'], 
                    $field_details['html_cb'],
                    $this->page_slug, 
                    $section_id
                );
            }
        }
    }
    
    public function getOptions() {
        return get_option($this->settings_group_name);
    }
    
    public function getThumbnailName() {
        
        $options = $this->getOptions();
        if (!isset($options['image_size_name']) || !$options['image_size_name']) {
            return 'slider';
        }
        return $options['image_size_name'];
    }
    
    public function getSettingFormPart($setting_name, $type) {
        
        return self::getPartial("setting-$type", array(
            'instance' => $this,
            'setting_name' => $setting_name
        ));
    }
    
    public function echoSettingImageSizeName() {
        
        echo $this->getSettingFormPart('image_size_name', 'input');
    }
    
    public function echoSettingsDescription() {
        echo '<p>General slider settings</p>';
    }
    
    public function validateSettings($input) {
        
        $input['image_size_name'] = strip_tags($input['image_size_name']);
        return $input;
    }
    
    // adds custom columns to slider images list
    public function manage_posts_columns($defaults) {
        
        $defaults[$this->column_name_caption] = __('Caption', self::DOMAIN);
        $defaults[$this->column_name_link] = __('Link', self::DOMAIN);
        $defaults[$this->column_name_order] = __('Order', self::DOMAIN);
        $defaults[$this->column_name_thumb] = __('Thumb', self::DOMAIN);
        
        return $defaults;
    }
    
    // shows custom column content in slider images list
    public function manage_posts_custom_column($column_name, $post_id) {
        
        if ($column_name == $this->column_name_caption) {  
        
            echo get_post_meta($post_id, $this->meta_name_caption, true);
            
        } else if ($column_name == $this->column_name_link) {
        
            echo get_post_meta($post_id, $this->meta_name_link, true);
            
        } else if ($column_name == $this->column_name_order) {
            
            echo get_post_meta($post_id, $this->meta_name_order, true);
            
        } else if ($column_name == $this->column_name_thumb) {
            
            $src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'thumbnail');
            $src = $src[0];
            $domain = self::DOMAIN;
            echo self::getPartial('thumb', array(
                'src' => $src,
                'domain' => $domain,
                'post_id' => $post_id,
            ));
        }
    }
    
    public function manage_edit_sortable_columns($columns) {
        
        $columns[$this->column_name_caption] = $this->column_name_caption;
        $columns[$this->column_name_link] = $this->column_name_link;
        $columns[$this->column_name_order] = $this->column_name_order;
        return $columns;
    }
    
    public function load_edit_php() {
        add_filter('request', array($this, 'request'));
    }
    
    // modify request to sort columns
    public function request($vars) {
        
        if (isset($vars['post_type']) && ($this->post_type == $vars['post_type'])) {

            if (isset($vars['orderby']) && 
                in_array($vars['orderby'], array(
                    $this->column_name_link, 
                    $this->column_name_caption,
                    $this->column_name_order))) {

                $orderby = 'meta_value';
                if ($vars['orderby'] == $this->column_name_order) {
                    $orderby = 'meta_value_num';
                }
                
                $vars = array_merge(
                    $vars,
                    array(
                        'meta_key' => $vars['orderby'],
                        'orderby' => $orderby,
                    )
                );
            }
        }
        return $vars;
    }
    
    public function save_post($post_id) {
        
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        if (!wp_verify_nonce($_POST[self::NONCE], $this->plugin_basename)) {
            return;
        }

        if (!current_user_can('edit_page', $post_id)) {
            return;
        }   

        // Check permissions
        if ($_POST['post_type'] != $this->post_type) {
            return;
        }

        // OK, we're authenticated: we need to find and save the data

        $link = esc_url($_POST[$this->meta_name_link]);
        $caption = strip_tags($_POST[$this->meta_name_caption]);
        $order = (int)($_POST[$this->meta_name_order]);
        if (!$order) {
            $order = 999;
        }

        update_post_meta($post_id, $this->meta_name_link, $link);
        update_post_meta($post_id, $this->meta_name_caption, $caption);
        update_post_meta($post_id, $this->meta_name_order, $order);
    }
    
    public function register_meta_box_cb() {
        add_meta_box(
            $this->meta_name_caption,
            __('Caption', self::DOMAIN), 
            array($this, 'echoCaptionMetaboxHtml'), 
            null, 
            'advanced', 
            'high'
        );
        add_meta_box(
            $this->meta_name_link,
            __('Link', self::DOMAIN),
            array($this, 'echoLinkMetaboxHtml'), 
            null, 
            'advanced', 
            'high'
        );
        add_meta_box(
            $this->meta_name_order,
            __('Order', self::DOMAIN), 
            array($this, 'echoOrderMetaboxHtml'), 
            null, 
            'advanced', 
            'high'
        );
    }
    
    public function echoCaptionMetaboxHtml($post) {
        $caption = get_post_meta($post->ID, $this->meta_name_caption, true);
        wp_nonce_field($this->plugin_basename, self::NONCE);
        echo self::getPartial('caption', array(
            'meta_name_caption' => $this->meta_name_caption,
            'caption' => $caption,
        ));
    }
    
    public function echoOrderMetaboxHtml($post) {
        $order = get_post_meta($post->ID, $this->meta_name_order, true);
        wp_nonce_field($this->plugin_basename, self::NONCE);
        echo self::getPartial('order', array(
            'meta_name_order' => $this->meta_name_order,
            'order' => $order,
        ));
    }
    
    public function echoLinkMetaboxHtml($post) {
        $link = get_post_meta($post->ID, $this->meta_name_link, true);
        wp_nonce_field($this->plugin_basename, self::NONCE);
        echo self::getPartial('link', array(
            'meta_name_link' => $this->meta_name_link,
            'link' => $link,
        ));
    }
    
    public function init() {
        register_post_type($this->post_type,
            array(
                'labels' => array(
                    'name' => __('Dvs Slider Images', self::DOMAIN),
                    'singular_name' => __('Dvs Slider Image', self::DOMAIN),
                    'add_new' => __('Add New', self::DOMAIN),
                    'add_new_item' => __('Add New Dvs Slider Image', self::DOMAIN),
                    'edit_item' => __('Edit Dvs Slider Image', self::DOMAIN),
                    'new_item' => __('New Dvs Slider Image', self::DOMAIN),
                    'view_item' => __('View Dvs Slider Image', self::DOMAIN),
                    'search_items' => __('Search Dvs Slider Images', self::DOMAIN),
                    'not_found' => __('No Dvs Slider Images Found', self::DOMAIN),
                    'not_found_in_trash' => __('No Dvs Slider Images Found In Trash', self::DOMAIN),
                    'parent_item_colon' => __('Parent Dvs Slider Image', self::DOMAIN),
                    'all_items' => __('All Dvs Slider Images', self::DOMAIN),
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'dvs-slider-images'),
                'supports' => array(
                    'title', 
                    //'editor', 
                    //'comments', 
                    //'revisions', 
                    //'trackbacks', 
                    //'author',
                    //'excerpt', 
                    //'page-attributes', 
                    'thumbnail', 
                    //'custom-fields',
                ),
                'register_meta_box_cb' => array($this, 'register_meta_box_cb'),
            )
        );
    }
    
    public function getSliderHtml() {
        
        $base_url = plugin_dir_url($this->base_file);
        
        wp_enqueue_style(self::DOMAIN."_default", "{$base_url}themes/default/default.css");
        wp_enqueue_style(self::DOMAIN."_light", "{$base_url}themes/light/light.css");
        wp_enqueue_style(self::DOMAIN."_dark", "{$base_url}themes/dark/dark.css");
        wp_enqueue_style(self::DOMAIN."_bar", "{$base_url}themes/bar/bar.css");
        wp_enqueue_style(self::DOMAIN."_nivo_slider", "{$base_url}css/nivo-slider.css");
        wp_enqueue_style(self::DOMAIN."_style", "{$base_url}css/style.css");
        
        wp_enqueue_script(self::DOMAIN."_nivo_slider", "{$base_url}js/jquery.nivo.slider.js", array('jquery'), false, true);
        wp_enqueue_script(self::DOMAIN."_init", "{$base_url}js/init.js", array(self::DOMAIN."_nivo_slider"), false, true);

        $query = new WP_Query(array(
            'post_type' => $this->post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_key' => $this->meta_name_order,
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
        ));
        $slider_images = $query->get_posts();
        $thumbnail_name = $this->getThumbnailName();
        $slider_items = array();
        foreach ($slider_images as $slider_image) {
            
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($slider_image->ID), $thumbnail_name);
            $image_src = $image[0];
            
            $slider_items[] = array(
                'link' => get_post_meta($slider_image->ID, $this->meta_name_link, true),
                'caption' => get_post_meta($slider_image->ID, $this->meta_name_caption, true),
                'image_src' => $image_src,
            );
        }
        
        return self::getPartial('slider', array(
            'slider_items' => $slider_items,
        ));
    }
    
    private static function getPartial($name, $params) {
        ob_start();
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        require "partials/$name.php";
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
