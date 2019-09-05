<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <?php if ($action == 'create') : ?>
                <div class="form-group <?= form_error('user_name') ? 'has-error' : '' ?>">
                    <label>用户名称</label>
                    <input type="text" name="user_name" class="form-control" placeholder="Enter ..." value="<?= isset($row['user_name']) ? $row['user_name'] : '' ?>">
                    <?= form_error('user_name', '<span class="help-block">', '</span>') ?>
                </div>
            <?php else : ?>
                <input type="hidden" name="uid" value="<?= $row['uid'] ?>">
            <?php endif; ?>
            <div class="form-group <?= form_error('bank_real_name') ? 'has-error' : '' ?>">
                <label>银行卡姓名</label>
                <input type="text" name="bank_real_name" class="form-control" placeholder="Enter ..." value="<?= isset($row['bank_real_name']) ? $row['bank_real_name'] : '' ?>">
                <?= form_error('bank_real_name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('bank_name') ? 'has-error' : '' ?>">
                <label>银行名称</label>
                <input type="text" name="bank_name" class="form-control" placeholder="Enter ..." value="<?= isset($row['bank_name']) ? $row['bank_name'] : '' ?>">
                <?= form_error('bank_name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('bank_account') ? 'has-error' : '' ?>">
                <label>银行卡账号</label>
                <input type="text" name="bank_account" class="form-control" placeholder="Enter ..." value="<?= isset($row['bank_account']) ? $row['bank_account'] : '' ?>">
                <?= form_error('bank_account', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('bank_address') ? 'has-error' : '' ?>">
                <label>开户地址</label>
                <input type="text" name="bank_address" class="form-control" placeholder="Enter ..." value="<?= isset($row['bank_address']) ? $row['bank_address'] : '' ?>">
                <?= form_error('bank_address', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->