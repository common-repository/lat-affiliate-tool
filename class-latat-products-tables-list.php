<?php

if (!class_exists('WP_List_Table')) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('LATAT_Products_Tables_List')) {
  class LATAT_Products_Tables_List extends WP_List_Table {

    /** Class constructor */
    public function __construct() {

      parent::__construct(
        array(
          'singular' => __('Table', 'latat'), //singular name of the listed records
          'plural' => __('Tables', 'latat'), //plural name of the listed records
          'ajax' => false, //should this table support ajax?
        )
      );
    }

    /**
     * Retrieve table’s product list from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_tables($per_page = 10, $page_number = 1) {
      $data_query = array(
        'numberposts' => $per_page,
        'orderby' => isset($_REQUEST['orderby']) && !empty($_REQUEST['orderby']) ? esc_sql($_REQUEST['orderby']) : null,
        'order' => !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC',
        'post_type' => 'paa_table',
        'offset' => ($page_number - 1) * $per_page,
      );

      $tables = get_posts($data_query);

      $result = array_map(function ($table) {
        return array(
          'ID' => $table->ID,
          'title' => $table->post_title,
          'shortcode' => '<code>[latat_table table_id="' . $table->ID . '"/]</code>',
        );
      }, $tables);

      return $result;
    }

    /**
     * Delete a table record.
     *
     * @param int $id table ID
     */
    public static function delete_table($id) {
      wp_delete_post($id, true);
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
      return wp_count_posts('paa_table')->publish;
    }

    /** Text displayed when no table data is available */
    public function no_tables() {
      _e('No tables avaliable.', 'latat');
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_name($item) {

      // create a nonce
      $delete_nonce = wp_create_nonce('latat_delete_table');

      $title = '<strong>' . $item['title'] . '</strong>';

      $actions = [
        'edit' => sprintf('<a href="?page=%s&tab=%s&table_id=%s">Edit</a>', esc_attr($_REQUEST['page']), 'edit', absint($item['ID'])),
        'trash' => sprintf('<a href="?page=%s&action=%s&table_id=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce),
      ];
      return $title . $this->row_actions($actions);
    }

    /**
     * Render a column when no column specific method exists.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name) {
      switch ($column_name) {
      case 'shortcode':
        return $item[$column_name];
      case 'title':
        return $this->column_name($item);
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
      }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item) {
      return sprintf(
        '<input type="checkbox" name="bulk-delete[]" value="%s" />',
        $item['ID']
      );
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
      $columns = [
        'cb' => '<input type="checkbox" />',
        'title' => __('Title', 'latat'),
        'shortcode' => __('Shortcode', 'latat'),
      ];

      return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
      $sortable_columns = array(
        'title' => array('title', true),
        'shortcode' => array('shortcode', false),
      );

      return $sortable_columns;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
      $actions = [
        'bulk-delete' => 'Delete',
      ];

      return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

      $this->_column_headers = $this->get_column_info();

      /** Process bulk action */
      $this->process_bulk_action();

      $per_page = $this->get_items_per_page('tables_per_page', 10);
      $current_page = $this->get_pagenum();
      $total_items = self::record_count();
      // $this->set_pagination_args([
      //     'total_items' => $total_items, //WE have to calculate the total number of items
      //     'per_page'    => $per_page //WE have to determine how many items to show on a page
      // ]);

      $this->set_pagination_args(array(
        'total_items' => $total_items, // total items defined above
        'per_page' => $per_page, // per page constant defined at top of method
        'total_pages' => ceil($total_items / $per_page), // calculate pages count
      ));
      $this->items = self::get_tables($per_page, $current_page);
    }

    public function process_bulk_action() {

      //Detect when a bulk action is being triggered...
      if ('delete' === $this->current_action()) {

        // In our file that handles the request, verify the nonce.
        $nonce = esc_attr($_REQUEST['_wpnonce']);
        if (!wp_verify_nonce($nonce, 'latat_delete_table')) {
          die('Go get a life script kiddies');
        } else {
          self::delete_table(absint($_GET['table_id']));

          if (headers_sent()) {
            echo "<script>location.href = '" . admin_url('admin.php?page=latat') . "';</script>";
            die("Redirect failed. Please click on this link: <a href=" . admin_url('admin.php?page=latat') . ">Refresh</a>");
          } else {
            wp_redirect(esc_url(add_query_arg()));
            exit;
          }
        }
      }

      // If the delete bulk action is triggered
      if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
        || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
      ) {

        $delete_ids = esc_sql($_POST['bulk-delete']);

        // loop over the array of record IDs and delete them
        foreach ($delete_ids as $id) {
          self::delete_table($id);
        }

        if (headers_sent()) {
          echo "<script>location.href = '" . admin_url('admin.php?page=latat') . "';</script>";
          die("Redirect failed. Please click on this link: <a href=" . admin_url('admin.php?page=latat') . ">Refresh</a>");
        } else {
          wp_redirect(esc_url(add_query_arg()));
          exit;
        }
      }
    }
  }
}
