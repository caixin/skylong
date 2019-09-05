<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group">
                <label>会员账号</label>
                <input type="text" name="user_name" class="form-control" value="<?= isset($row['user_name']) ? $row['user_name'] : '' ?>" disabled>
            </div>
        <?php if ($row['offline_channel'] == 1): ?>
            <div class="form-group">
                <label>汇款人姓名</label>
                <input type="text" name="offline_user_realname" class="form-control" value="<?= $row['offline_user_realname'] ?>" disabled>
            </div>
            <div class="form-group">
                <label>汇款银行名称</label>
                <input type="text" name="offline_user_bank_name" class="form-control" value="<?= $row['offline_user_bank_name'] ?>" disabled>
            </div>
        <?php endif; ?>
            <div class="form-group">
                <label>汇款金额</label>
                <input type="text" name="money" class="form-control" value="<?= $row['money'] ?>" disabled>
            </div>
        <?php if ($row['offline_channel'] == 1): ?>
            <div class="form-group">
                <label>汇款方式</label>
                <select name="offline_pay_type" class="form-control" disabled>
                <?php foreach (recharge_order_model::$offline_pay_typeList as $key => $val): ?>
                    <option value="<?=$key?>" <?=$row['offline_pay_type'] == $key ? 'selected':''?>><?=$val?></option>
                <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select id="status" name="status" class="form-control">
                    <option value="1">通过</option>
                    <option value="2">不通过</option>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <div id="remark_div" class="form-group <?= form_error('check_remarks') ? 'has-error' : '' ?>">
                <label>备注</label>
                <input type="text" name="check_remarks" class="form-control" placeholder="Enter ..." value="<?= $row['check_remarks'] ?>">
                <?= form_error('check_remarks', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<script>
    $('#status').change(function() {
        if ($(this).val() == 1) {
            $('#remark_div').hide();
        } else {
            $('#remark_div').show();
        }
    });
    $('#status').change();
</script>