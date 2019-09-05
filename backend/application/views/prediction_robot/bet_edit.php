<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('bet_money') ? 'has-error' : '' ?>">
                <label>下注金额</label>
                <input type="number" name="bet_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['bet_money']) ? $row['bet_money'] : '' ?>">
                <?= form_error('bet_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('bet_money_max') ? 'has-error' : '' ?>">
                <label>投注总额</label>
                <input type="number" name="bet_money_max" class="form-control" placeholder="Enter ..." value="<?= isset($row['bet_money_max']) ? $row['bet_money_max'] : '' ?>">
                <?= form_error('bet_money_max', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->