<?php
require 'class-latat-products-tables-list.php';
if (!class_exists('LATAT_Admin_Pages')) {
  class LATAT_Admin_Pages {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $options_group = 'latat_settings';
    private $options_name = LATAT_PAA_CREDENTIAL_OPTION_NAME;
    public $tables_obj;

    /**2
     * Start up
     */
    public function __construct() {
      add_filter('set-screen-option', array($this, 'set_screen'), 10, 3);
      add_action('admin_enqueue_scripts', array($this, 'reg_css_and_js'));
      add_action('admin_menu', array($this, 'add_plugin_page'));
      add_action('admin_init', array($this, 'page_init'));
    }
    public function set_screen($status, $option, $value) {
      return $value;
    }
    // Update CSS within in Admin
    public function reg_css_and_js($hook) {
      $current_screen = get_current_screen();
      if (strpos($current_screen->base, 'latat') === false) {
        return;
      } else {
        wp_enqueue_style('fontawesome_css', plugins_url('assets/fontawesome-free-5.9.0-web/css/all.min.css', __FILE__));
        wp_enqueue_style('bulma_css', plugins_url('assets/css/bulma.min.css', __FILE__));
        wp_enqueue_style('trumbowyg_css', plugins_url('assets/trumbowyg/ui/trumbowyg.min.css', __FILE__));
        wp_enqueue_style('latat_css', plugins_url('assets/css/style.css', __FILE__), time());
        wp_enqueue_script('latat_script', plugins_url('assets/js/script.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), time(), true);
        wp_enqueue_script('trumbowyg_js', plugins_url('assets/trumbowyg/trumbowyg.min.js', __FILE__), time(), true);
      }
    }

    public function get_field_name($name) {
      $options_name = $this->options_name;
      return $options_name . '[' . $name . ']';
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
      // This page will be under "Settings"
      $hook = add_menu_page(
        'Product advertising amazon', // Menu title.
        'LAT Affiliate Tool',
        'manage_options',
        'latat'
      );

      add_submenu_page(
        'latat',
        'LAT Affiliate Tool',
        'All tables',
        'manage_options',
        'latat',
        array($this, 'create_admin_table_list_page')
      );

      add_submenu_page(
        'latat',
        'LAT Affiliate Tool',
        'Configs',
        'manage_options',
        'latat_settings',
        array($this, 'create_admin_settings_page')
      );

      add_submenu_page(
        'latat',
        'LAT Affiliate Tool',
        'Usage',
        'manage_options',
        'latat_usage',
        array($this, 'create_admin_usage_page')
      );

      add_action("load-$hook", array($this, 'screen_option'));
    }

    /**
     * Screen options
     */
    public function screen_option() {
      $option = 'per_page';
      $args = [
        'label' => 'Tables',
        'default' => 5,
        'option' => 'tables_per_page',
      ];

      add_screen_option($option, $args);
      $this->tables_obj = new LATAT_Products_Tables_List();
    }

    public function create_admin_table_list_page() {
      $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';
      echo '<div class="wrap">';
      echo '<h1 style="font-weight: 600">' . get_admin_page_title() . '</h1>';

      switch ($tab) {
      # all tables
      default:
        // prepare scripts
        wp_localize_script(
          'latat_script',
          'latat_ajax_object',
          array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'edit_table_url' => admin_url('admin.php?page=latat&tab=edit&table_id='),
            'tab' => $tab,
          )
        );
        include 'all-tables-page.php';
        break;

      # edit page
      case 'edit':
        include 'edit-table-page.php';
        break;
      }
      echo '</div>'; // end wrap
    }

    /**
     * Options page callback
     */
    public function create_admin_settings_page() {
      $this->options = get_option($this->options_name);
      echo '<div class="wrap">';
      echo '<h1 style="font-weight: 600">' . get_admin_page_title() . '</h1>';
      echo '<form method="post" action="options.php">';
      settings_fields($this->options_group); // This prints out all hidden setting fields
      do_settings_sections('latat_settings');
      submit_button();
      echo '</form>';
      echo '</div>';
    }

    /**
     * Options page callback
     */
    public function create_admin_usage_page() {
      // $this->options = get_option($this->options_name);
      echo '<div class="wrap">';
      echo '<h1 style="font-weight: 600">' . get_admin_page_title() . '</h1>';
      echo '<h3 style="font-weight: 600"> Demo </h3>';
      echo '<div method="post" action="options.php">';
      echo '<p>';
      echo '[latat_single asin="B01L1OPIBO" title="..." background="..." color="..." /]';
      // settings_fields($this->options_group); // This prints out all hidden setting fields
      // do_settings_sections('latat_settings');
      submit_button();
      echo '</p>';
      echo '</div>';
      echo '</div>';
    }

    /**
     * Register and add settings
     */
    public function page_init() {
      register_setting(
        $this->options_group, // Option group
        $this->options_name, // Option name
        array($this, 'sanitize') // Sanitize
      );
      add_settings_section(
        'paa_tokens_configs', // ID
        'Product Advertising Api credentials', // Title
        array($this, 'print_paa_credentials_configs_section_info'), // Callback
        'latat_settings' // Page
      );
      array_map(function ($field) {
        add_settings_field(
          $field['field_id'], // ID
          $field['field_title'], // Title
          array($this, $field['field_cb']), // Callback
          'latat_settings', // Page
          'paa_tokens_configs', // Section
          array(
            'field_id' => $field['field_id'],
          )
        );
      }, array(
        array(
          'field_id' => 'access_key',
          'field_title' => 'Access key',
          'field_cb' => 'text_field_cb',
        ),
        array(
          'field_id' => 'secret_key',
          'field_title' => 'Secret key',
          'field_cb' => 'text_field_cb',
        ),
        array(
          'field_id' => 'partner_tag',
          'field_title' => 'Partner tag',
          'field_cb' => 'text_field_cb',
        ),
        array(
          'field_id' => 'custom_css',
          'field_title' => 'Custom CSS',
          'field_cb' => 'textarea_cb',
        ),
      ));
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
      $new_input = array();
      if (isset($input['access_key'])) {
        $new_input['access_key'] = sanitize_text_field($input['access_key']);
      }

      if (isset($input['secret_key'])) {
        $new_input['secret_key'] = sanitize_text_field($input['secret_key']);
      }

      if (isset($input['partner_tag'])) {
        $new_input['partner_tag'] = sanitize_text_field($input['partner_tag']);
      }

      if (isset($input['custom_css'])) {
        $new_input['custom_css'] = sanitize_textarea_field($input['custom_css']);
      }

      return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_paa_credentials_configs_section_info() {
      _e('Enter your credentials details below:', 'latat');
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function text_field_cb($args) {
      printf(
        '<input type="text" id="%s" name="%s" value="%s" style="width: 600px"/>',
        $args['field_id'],
        $this->get_field_name($args['field_id']),
        isset($this->options[$args['field_id']]) ? esc_attr($this->options[$args['field_id']]) : ''
      );
    }

    public function textarea_cb($args) {
      printf(
        '<textarea id="%s" name="%s" rows="15">%s</textarea>',
        $args['field_id'],
        $this->get_field_name($args['field_id']),
        $this->options[$args['field_id']]
      );
    }
  }

  if (is_admin()) {
    $my_settings_page = new LATAT_Admin_Pages();
  }
}