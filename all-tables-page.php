<div style="display:flex; justify-content: space-between; align-items: center; margin-top: 10px">
  <h2 style="color: #666; font-size: 18px">All tables</h2>
  <div>
    <button id="btn-open-create-form" class="button button-primary">
      <i class="fa fa-plus" style="margin-right: 8px"></i>New table
    </button>
  </div>
</div>
<?php include 'create-table-modal.php';?>
<form method="post">
  <?php
$this->tables_obj->prepare_items();
$this->tables_obj->display();
?>
</form>