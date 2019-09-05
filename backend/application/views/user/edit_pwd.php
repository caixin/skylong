<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group">
                <label>用户名称</label>
                <input type="text" class="form-control" value="<?= $row['user_name'] ?>" disabled>
            </div>
            <div class="form-group <?= form_error('user_pwd') ? 'has-error' : '' ?>">
                <label>用户密码 <span style="color:red;">【请输入英数6至12码，空白=不修改】</span></label>
                <input type="password" name="user_pwd" class="form-control" placeholder="Enter ..." value="">
                <?= form_error('user_pwd', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('security_pwd') ? 'has-error' : '' ?>">
                <label>提现密码 <span style="color:red;">【请输入纯数字6码，空白=不修改】</span></label>
                <input type="password" name="security_pwd" class="form-control" placeholder="Enter ..." value="">
                <?= form_error('security_pwd', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->