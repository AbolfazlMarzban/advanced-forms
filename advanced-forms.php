<?php
/*
 * Plugin Name: Advanced Forms
 * Description: Advanced Forms
 * Author: Abolfazl Marzban
 * Author URI : https://abolfazlmarzban.ir
 * Version: 1.0.0
 * Text Domain: Advanced-forms
 */

if (!defined('ABSPATH')) {
    exit;
}

class advancedForms
{
    public function __construct()
    {
        //Create cutome post type
        add_action('init', array($this, 'create_custom_post_type'));

        //add assets(css, js, etc)
        add_action('wp_enqueue_scripts', array($this, 'load_assets'));

        //add shortcode
        add_shortcode('advanced-forms', array($this, 'load_shortcode'));

        //add javascript
        add_action('wp_footer', array($this, 'load_scripts'));

        //Register Rest API
        add_action('rest_api_init', array($this, 'register_rest_api'));

    }

    public function create_custom_post_type()
    {
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                'name' => 'advanced forms',
                'singular_name' => 'advanced form Entry'
            ),
            'menu_icon' => 'dashicons-media-document',
        );

        register_post_type('advanced forms', $args);
    }

    public function load_assets()
    {
        wp_enqueue_style(
            'advanced-forms',
            plugin_dir_url(__FILE__) . 'css/advancedforms.css',
            array(),
            1,
            'all'
        );
        wp_enqueue_script(
            'advanced-forms',
            plugin_dir_url(__FILE__) . 'js/advancedforms.js',
            array('jquery'),
            1,
            true
        );
    }

    public function load_shortcode()
    { ?>

        <div class="simple-contact-form">
            <h1>send us an email</h1>
            <p>please fill the below form</p>
            <form id="simple-contact-form__form">

                <div class="form-group mb-2">
                    <input type="text" name="name" placeholder="Name" class="form-control">
                </div>

                <div class="form-group mb-2">
                    <input type="email" name="email" placeholder="Email" class="form-control">
                </div>

                <div class="form-group mb-2">
                    <input type="tel" name="phone" placeholder="Phone" class="form-control">
                </div>

                <div class="form-group mb-2">
                    <textarea name="message" placeholder="Type your message" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-block w-100">Send Message</button>
                </div>
            </form>
        </div>

    <?php }

    public function load_scripts()
    { ?>
        <script>

            var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
            (function ($) {
                $('#simple-contact-form__form').submit(function (event) {
                    event.preventDefault()
                    var form = $(this).serialize()
                    console.log('form', form)
                    $.ajax({
                        method: 'post',
                        url: '<?php echo get_rest_url(null, 'advanced-forms/v1/send-form'); ?>',
                        headers: { 'X-WP-Nonce': nonce },
                        data: form
                    });
                })
            })(jQuery)

        </script>

    <?php }

    public function register_rest_api()
    {
        register_rest_route('advanced-forms/v1', 'send-form', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_contact_form')
        ));

    }


    public function handle_contact_form($data)
    {
        $headers = $data->get_headers();
        $params = $data->get_params();
        $nonce = $headers['x_wp_nonce'][0];

        if(!wp_verify_nonce($nonce, 'wp_rest')){
           return new WP_REST_Response('Message not sent', 422);
        }

        $post_id = wp_insert_post([
            'post_type' => 'advanced forms',
            'post_title' => 'Form enquiry',
            'post_status' => 'publish'
        ]);

        if($post_id){
            return new WP_REST_Response('Thank you for your email', 200);
        }
        
    }

}

new advancedForms;