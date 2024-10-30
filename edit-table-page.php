<?php

require 'utils.php';

$post_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : '';

$table_data = array(
  'display_style' => 3,
  'products' => array(),
  'post_id' => $post_id,
);

if ($post_id != '') {
  $post_data = get_post($post_id);
  // echo '<pre>';
  // print_r($post_data);
  // echo PHP_EOL . '--------------------------------' . PHP_EOL;
  // print_r(base64_decode($post_data->post_content));
  // echo PHP_EOL . '--------------------------------' . PHP_EOL;
  // print_r(json_decode(base64_decode($post_data->post_content), true));
  // echo '</pre>';
  $utils = new LATAT_Ultils();
  $content = $utils->decodeData($post_data->post_content);
  $table_data = array_replace($table_data, $content ? $content : []);
  $table_data['post_title'] = $post_data->post_title;
}

// prepare scripts
wp_localize_script(
  'latat_script',
  'latat_ajax_object',
  array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'edit_table_url' => admin_url('admin.php?page=latat&tab=edit&table_id='),
    'tab' => $tab,
    'table_data' => $table_data,
  )
);

// display style selector
$display_style = '';
for ($i = 1; $i <= 4; $i++) {
  $checked = ($table_data['display_style'] === $i) ? ' checked' : '';
  $disabled = ($i != 3) ? 'disabled' : '';
  $display_style .= '<input type="radio" name="display-style" id="style-' . $i . '" value="' . $i . '" ' . $checked . ' ' . $disabled . '>';
  $display_style .= '<label class="' . $disabled . '" for="style-' . $i . '">';
  $display_style .= '<img src="' . plugins_url("assets/imgs/style-" . $i . ".png", __FILE__) . '" alt="">';
  $display_style .= '<span>Style ' . $i . '</span>';
  $display_style .= '</label>';
}
?>

<div style="display:flex; justify-content: space-between; align-items: center; margin-top: 10px">
  <h2 style="color: #666; font-size: 18px">Edit table</h2>
</div>
<div style="margin-top: 10px; margin-bottom: 10px;">
  <button id="btn-open-search-modal" class="button "><i class="fa fa-plus" style="margin-right: 8px"></i>Add products</button>
</div>
<div class="columns is-mobile">
  <div class="column is-three-quarters">
    <?php include 'add-product-modal.php';?>
    <?php include 'table-products-list.php';?>
  </div>
  <div class="column">
    <div class="card" style="margin-top: 0">
      <fieldset style="margin-bottom: 10px">
        <label>Table name</label>
        <input type="text" id="post_title" value="<?php echo $table_data['post_title']; ?>" style="width: 100%;">
      </fieldset>
      <fieldset style="margin-bottom: 10px">
        <label>Shortcode</label>
        <input type="text" readonly value="[latat_table table_id='<?php echo $post_id; ?>'/]" style="width: 100%;">
      </fieldset>
      <div class="table-style" style="margin-bottom: 10px">
        <div>Table style</div>
        <div class="latat-meta-box-body" style="background: #fff; padding: 0">
          <?php echo $display_style; ?>
        </div>
      </div>
    </div>
    <div style="margin-top: 20px">
      <button id="btn-save-table" class="button button-primary" role="button" style="margin-right: 10px;">
        <i class="fa fa-check" style="margin-right: 8px"></i>Save changes
      </button>
      <div class="edit-table-noti notification is-danger" id="noti-danger" style="margin-top: 5px">Error, please try again later!</div>
    </div>
</div>