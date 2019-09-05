<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <input type="hidden" name="lottery_id" value="<?= isset($row['lottery_id']) ? $row['lottery_id'] : '' ?>">
            <input type="hidden" name="qishu" value="<?= isset($row['qishu']) ? $row['qishu'] : '' ?>">
            <div class="form-group <?= form_error('numbers') ? 'has-error' : '' ?>">
                <label>开奖号码</label>
                <input type="text" name="numbers" class="form-control" placeholder="Enter ..." value="<?= isset($row['numbers']) ? $row['numbers'] : '' ?>">
                <?= form_error('numbers', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->