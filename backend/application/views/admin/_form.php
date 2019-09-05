<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('mobile') ? 'has-error' : '' ?>">
                <label>手机号码 <span style="color:red;">【前后台登入用】</span></label>
                <input type="text" name="mobile" class="form-control" placeholder="Enter ..." value="<?= isset($row['mobile']) ? $row['mobile'] : '' ?>">
                <?= form_error('mobile', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('password') ? 'has-error' : '' ?>">
                <label>用户密码 <span style="color:red;">【请输入英数6至12码】</span></label>
                <input type="password" name="password" class="form-control" placeholder="Enter ..." value="">
                <?= form_error('password', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('username') ? 'has-error' : '' ?>">
                <label>用户名称 <span style="color:red;"></span></label>
                <input type="text" name="username" class="form-control" placeholder="Enter ..." value="<?= isset($row['username']) ? $row['username'] : '' ?>">
                <?= form_error('username', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('roleid') ? 'has-error' : '' ?>">
                <label>角色群组</label>
                <select name="roleid" class="form-control">
                    <?php foreach ($role as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['roleid']) && $row['roleid'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('roleid', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('otp_check') ? 'has-error' : '' ?>">
                <label>OTP</label>
                <select name="otp_check" class="form-control">
                    <?php foreach (admin_model::$otp_checkList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['otp_check']) && $row['otp_check'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('otp_check', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (admin_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <?php if ($action == 'create') : ?>
                <div class="form-group <?= form_error('is_agent') ? 'has-error' : '' ?>">
                    <div class="checkbox">
                        <input type="hidden" name="is_agent" value="0">
                        <label>
                            <input type="checkbox" id="is_agent" name="is_agent" value="1" <?= isset($row['is_agent']) && $row['is_agent'] == 1 ? 'checked' : '' ?>>
                            是否为代理<span style="color:red;">【勾选后会新增玩家代理帐号】</span>
                        </label>
                    </div>
                </div>
                <div id="div1" class="form-group <?= form_error('security_pwd') ? 'has-error' : '' ?>" style="display:none;">
                    <label>提现密码 <span style="color:red;">【请输入纯数字6码，非代理不需填写】</span></label>
                    <input type="password" name="security_pwd" class="form-control" placeholder="Enter ..." value="">
                    <?= form_error('security_pwd', '<span class="help-block">', '</span>') ?>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<script>
    $('#is_agent').click(function() {
        $(this).prop('checked') ? $('#div1').show() : $('#div1').hide();
    });
</script>