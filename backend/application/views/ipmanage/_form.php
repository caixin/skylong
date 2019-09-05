<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('ip') ? 'has-error' : '' ?>">
                <label>IP位置</label>
                <input type="text" name="ip" class="form-control" placeholder="Enter ..." value="<?= isset($row['ip']) ? $row['ip'] : '' ?>">
                <?= form_error('ip', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('note') ? 'has-error' : '' ?>">
                <label>备注</label>
                <input type="text" name="note" class="form-control" placeholder="Enter ..." value="<?= isset($row['note']) ? $row['note'] : '' ?>">
                <?= form_error('note', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->