<?php

/**
 * Plugin Name: Meta-box plugin
 * Plugin Uri: https://our-metabox
 * Description: This plugin will add some extra features that going to be very useful to your theme.
 * Author: SeJan ahmed BayZid
 * Version: 1.0
 * License: 
 * Text Domain: our-metabox
 * Domain path: /languages
 */

class OurMetabox {
    public function __construct(){
    
        add_action('plugins_loaded', array($this, 'omb_load_textdomain'));
        add_action('admin_menu', array($this, 'omb_add_metabox'));
        add_action('save_post', array($this, 'omb_save_metabox'));
        add_action('save_post', array($this, 'omb_save_image'));
        add_filter('user_contactmethods', array($this, 'omb_user_contact_method'));

        add_action('admin_enqueue_scripts', array($this, 'omb_admin_assets'));
    }

    // to add the metabox on WP users
    function omb_user_contact_method($methods)
    {
        $methods['facebook'] = __('Facebook', 'our-metabox');
        $methods['linkedin'] = __('Linked In', 'our-metabox');
        $methods['twitter'] = __('Twitter', 'our-metabox');

        return $methods;
    }

    function omb_admin_assets()
    {
        wp_enqueue_style('admin-style-css', plugin_dir_url(__FILE__) . 'assets/admin/css/input-style.css');
        wp_enqueue_style('jquery-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

        wp_enqueue_script('another-js', plugin_dir_url(__FILE__) . 'assets/admin/js/another.js', array('jquery', 'jquery-ui-datepicker'), '1.0', true);
    }


   /*  
    *    security checking and doing secured 
    */
    private function is_secured($nonce_field, $action, $post_id)
    {
        $nonce = isset($_POST[$nonce_field]) ? $_POST[$nonce_field] : '';

        if ($nonce == '') {
            return false;
        }
        if (!wp_verify_nonce($nonce, $action)) {
            return false;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }
        if (wp_is_post_autosave($post_id)) {
            return false;
        }
        if (wp_is_post_revision($post_id)) {
            return false;
        }

        return true;
    }
    //? saving image 
    function omb_save_image($post_id)
    {
        if (!$this->is_secured('omb_image_field', 'omb_image', $post_id)) {
            return $post_id;
        }

        $image_id = isset($_POST['omb_image_id']) ? $_POST['omb_image_id'] : '';
        $image_url = isset($_POST['omb_image_url']) ? $_POST['omb_image_url'] : '';

        update_post_meta($post_id, 'omb_image_id', $image_id);
        update_post_meta($post_id, 'omb_image_url', $image_url);
    }

    // saving value to metabox
    function omb_save_metabox($post_id)
    {
        if (!$this->is_secured('omb_location_field', 'omb_location', $post_id)) {
            return $post_id;
        }

        $location = isset($_POST['omb_location']) ? $_POST['omb_location'] : '';

        if ($location == '') {
            return $post_id;
        }

        $is_favorite = isset($_POST['omb_is_favorite']) ? $_POST['omb_is_favorite'] : '';
        /* color */
        $colors = isset($_POST['omb_clr']) ? $_POST['omb_clr'] : array();
        $color2 = isset($_POST['omb_color']) ? $_POST['omb_color'] : array();

        update_post_meta($post_id, 'omb_location', $location);
        update_post_meta($post_id, 'omb_is_favorite', $is_favorite);
        /* color */
        update_post_meta($post_id, 'omb_clr', $colors);
        update_post_meta($post_id, 'omb_color', $color2);
    }

    //? Registering the MetaBox
    function omb_add_metabox()
    {
        add_meta_box('omb_post_location', __('Location Info', 'our-metabox'), array($this, 'omb_display_metabox'), array('post', 'page'));

        add_meta_box('omb_book_info', __('Book Info', 'our-metabox'), array($this, 'omb_book_info'), array('books', 'post'));
        add_meta_box('omb_image_info', __('Image Info', 'our-metabox'), array($this, 'omb_image_info'), array('post'));
    }

    // for books custom query
  function omb_book_info() {
    wp_nonce_field('omb_book', 'omb_book_nonce');

  $metabox =  <<<EOD
    <div class="fields">
        <div class="field_c">
            <div class="label_c" >
            <label for="book_author">Book Author :</label>
            </div>
            <div class="input_c" id="book_author">
                <input type="text" class="widefat" id="book_author"/>
            </div>
        </div>   
        <div class="field_c">
            <div class="label_c" for="book_isbn">
            <label for="book_author">Book ISBN : </label>
            </div>
            <div class="input_c" >
                <input type="text" id="book_isbn"/>
            </div>
        </div> 
        <div class="field_c">
        <div class="label_c">
            <label for="publish_year">Publish Year : </label>
        </div>
        <div class="input_c">
            <input type="text" class="omb_dp" id="publish_year"/>
        </div>
        </div>
        <div class="float_c"/></div>
    </div>
 EOD;
        echo $metabox;
    }
    // Image uploading field  
    function omb_image_info($post)
    {
        $image_id = get_post_meta($post->ID, 'omb_image_id', true);
        $image_url = get_post_meta($post->ID, 'omb_image_url', true);

        $button_label = __('Upload An Image', 'our-metabox');
        wp_nonce_field('omb_image', 'omb_image_field');

        $metabox = <<<EOD
    <div class="fields">
        <div class="field_c">
            <p class="label_c" >
                <label>Image : </label>
            </p>
            <div class="input_c">
                <button type="text" id="upload_image"/> {$button_label} </button>
                <input type="hidden" name="omb_image_id" id="omb_image_id" value="{$image_id}"/>
                <input type="hidden" name="omb_image_url" id="omb_image_url" value="{$image_url} "/>
                <div id="image_container"></div>
            </div>
        </div> 
        <div class="float_c"/></div>
    </div>
EOD;

        echo $metabox;
    }

    //? displaying the metabox 
    function omb_display_metabox($post)
    {
        $location = get_post_meta($post->ID, 'omb_location', true);
        $is_favorite = get_post_meta($post->ID, 'omb_is_favorite', true);
        $checked = $is_favorite == 1 ? 'checked' : '';
        /* color */
        $saved_color = get_post_meta($post->ID, 'omb_clr', true);
        $saved_color2 = get_post_meta($post->ID, 'omb_color', true);


        $label = __('Location', 'our-metabox');
        $label2 = __('Is Favorite', 'our-metabox');


        $label3 = __('Colors : ', 'our-metabox');
        $colors = array('Red', 'Green', 'Yellow', 'Orange', 'Maroon', 'Black', 'Magenta');


        wp_nonce_field('omb_location', 'omb_location_field');
        $metabox = <<<EOD
        <p>
            <label for="omb_location">{$label}</label>
            <input type="text" name="omb_location" id="omb_location" value="{$location}">
        </p> 
        <p>
            <label for="omb_is_favorite">{$label2}</label>
            <input type="checkbox" name="omb_is_favorite" id="omb_is_favorite" value="1" {$checked}>
        </p>
         
        <p>
         <label>{$label3}</label>
    EOD;
        // checkbox field
        $saved_color = is_array($saved_color) ? $saved_color : array();
        foreach ($colors as $color) {
            $checked = in_array($color, $saved_color) ? 'checked' : '';
            $metabox .= <<<EOD
        <label for="omb_clr_{$color}"> {$color} </label>
        <input type="checkbox" name="omb_clr[]" id="omb_clr_{$color}" value = "{$color}" {$checked}/>
    EOD;
        }
        $metabox .= "</p>";


        $metabox .= <<<EOD
    <p>
    <label>{$label3}</label>  
EOD;    // radio field
        foreach ($colors as $color) {
            $checked = ($color == $saved_color2) ? 'checked' : '';
            $metabox .= <<<EOD
        <label for="omb_color_{$color}"> {$color} </label>
        <input type="radio" name="omb_color" id="omb_color" value="{$color}" {$checked}/>
    EOD;
        }
        $metabox .= "</p>";

        echo $metabox;
    }


    // textdomain 
    public function omb_load_textdomain()
    {
        load_plugin_textdomain('our-metabox', false, plugin_dir_url(__FILE__) . '/languages');
    }
}
new OurMetabox();
