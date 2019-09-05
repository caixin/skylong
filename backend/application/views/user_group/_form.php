<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('operator_id') ? 'has-error' : '' ?>">
                <label>运营商</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['operator_id']) && $row['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('operator_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>分层名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('min_extract_money') || form_error('max_extract_money') ? 'has-error' : '' ?>">
                <label>单次取款限额</label>
                <div class="input-group">
                    <input type="number" name="min_extract_money" class="form-control" style="width:45%" placeholder="Enter ..." value="<?= isset($row['min_extract_money']) ? $row['min_extract_money'] : '' ?>">
                    <label style="float:left;">&nbsp; 至 &nbsp;</label>
                    <input type="number" name="max_extract_money" class="form-control" style="width:45%" placeholder="Enter ..." value="<?= isset($row['max_extract_money']) ? $row['max_extract_money'] : '' ?>">
                </div>
                <?= form_error('min_extract_money', '<span class="help-block">', '</span>') ?>
                <?= form_error('max_extract_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('remark') ? 'has-error' : '' ?>">
                <label>备注</label>
                <input type="text" name="remark" class="form-control" placeholder="Enter ..." value="<?= isset($row['remark']) ? $row['remark'] : '' ?>">
                <?= form_error('remark', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (user_group_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<script>
    function checkall(k, attr) {
        $('input:checkbox[' + attr + '="' + k + '"]').each(function() {
            $(this).prop('checked', $('#' + attr + '_' + k).prop('checked'));
        });
    }

    function cancelcheckall(k, attr) {
        $('input:checkbox[' + attr + '="' + k + '"]').each(function() {
            if ($('#' + attr + '_' + k).prop('checked') == false) {
                $(this).prop('checked', false);
            }
        });
    }

    function group_check(k, attr) {
        var prop = false;
        $('input:checkbox[' + attr + '="' + k + '"]').each(function() {
            if ($(this).prop('checked')) prop = true;
        });

        $('#' + attr + '_' + k).prop('checked', prop).change();
    }
</script>