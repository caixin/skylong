<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group">
                <label>用户名称</label>
                <input type="text" class="form-control" value="<?= $row['user_name'] ?>" disabled>
            </div>
            <div class="form-group">
                <label>账户余额</label>
                <input type="text" class="form-control" value="<?= $row['money'] ?>" disabled>
            </div>
            <div class="form-group <?= form_error('money_type') ? 'has-error' : '' ?>">
                <label>帳戶类型</label>
                <select name="money_type" class="form-control">
                    <?php foreach (user_model::$moneyTypeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= $row['money_type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('money_type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('add_money') ? 'has-error' : '' ?>">
                <label>操作金额 <span style="color:red;">【>=0.01】</span></label>
                <input type="number" name="add_money" class="form-control" placeholder="Enter ..." value="<?= $row['multiple'] ?>" min="0.01" step="0.01">
                <?= form_error('add_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>操作类型</label>
                <select name="type" class="form-control">
                    <?php foreach (user_money_log_model::$typeList as $key => $val) : ?>
                        <?php if (in_array($key, [2, 3, 8])) : ?>
                            <option value="<?= $key ?>" <?= $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('multiple') ? 'has-error' : '' ?>">
                <label>打码量倍数 <span style="color:red;">【打码量 =（本金+彩金）* 打码量倍数，倍数为0则不计算打码量】</span></label>
                <input type="number" name="multiple" class="form-control" placeholder="Enter ..." value="<?= $row['multiple'] ?>" min="0">
                <?= form_error('multiple', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('remark') ? 'has-error' : '' ?>">
                <label>操作备注</label>
                <input type="text" name="remark" class="form-control" placeholder="Enter ..." value="<?= $row['remark'] ?>">
                <?= form_error('remark', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->