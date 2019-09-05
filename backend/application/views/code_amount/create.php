<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('money_type') ? 'has-error' : '' ?>">
                <label>帐户类型</label>
                <select name="money_type" class="form-control">
                    <?php foreach (user_model::$moneyTypeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['money_type']) && $row['money_type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('money_type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('user_name') ? 'has-error' : '' ?>">
                <label>用户名</label>
                <input type="text" name="user_name" class="form-control" placeholder="Enter ..." value="<?= isset($row['user_name']) ? $row['user_name'] : '' ?>">
                <?= form_error('user_name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('code_amount') ? 'has-error' : '' ?>">
                <label>变动打码量</label>
                <input type="number" name="code_amount" class="form-control" placeholder="Enter ..." value="<?= isset($row['code_amount']) ? $row['code_amount'] : '' ?>" min="0.01" step="0.01">
                <?= form_error('code_amount', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>操作类型</label>
                <select name="type" class="form-control">
                    <?php foreach (code_amount_log_model::$typeList as $key => $val) : ?>
                        <?php if (in_array($key, [1, 2])) : ?>
                            <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('description') ? 'has-error' : '' ?>">
                <label>操作备注</label>
                <input type="text" name="description" class="form-control" placeholder="Enter ..." value="<?= isset($row['description']) ? $row['description'] : '' ?>">
                <?= form_error('description', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->