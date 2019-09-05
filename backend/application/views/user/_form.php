<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <input type="hidden" name="operator_id" value="<?= $row['operator_id'] ?>">
        <?php if ($this->session->userdata('roleid') == 1 && $action != 'detail'): ?>
            <div class="form-group <?= form_error('user_name') ? 'has-error' : '' ?>">
                <div class="checkbox">
                    <input type="hidden" name="super_user" value="0">
                    <label>
                        <input type="checkbox" id="super_user" name="super_user" value="1">
                        超级用户 <span style="color:red;">【勾选后可通行各个营运商并自动加入白名单】</span>
                    </label>
                </div>
            </div>
        <?php endif; ?>
            <div class="form-group <?= form_error('user_name') ? 'has-error' : '' ?>">
                <label>用户名称</label>
                <input type="text" name="user_name" class="form-control" placeholder="Enter ..." value="<?= $row['user_name'] ?>" <?= $action == 'detail' ? 'disabled' : '' ?>>
                <?= form_error('user_name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('real_name') ? 'has-error' : '' ?>">
                <label>姓名</label>
                <input type="text" name="real_name" class="form-control" placeholder="Enter ..." value="<?= $row['real_name'] ?>" <?= $action == 'detail' ? 'disabled' : '' ?>>
                <?= form_error('real_name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('mobile') ? 'has-error' : '' ?>">
                <label>手机号码</label>
                <input type="text" name="mobile" class="form-control" placeholder="Enter ..." value="<?= $row['mobile'] ?>" <?= $action == 'detail' ? 'disabled' : '' ?>>
                <?= form_error('mobile', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('user_group_id') ? 'has-error' : '' ?>">
                <label>所属分层</label>
                <select name="user_group_id" class="form-control" <?= $action == 'detail' ? 'disabled' : '' ?>>
                    <?php foreach ($user_group as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= $row['user_group_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('user_group_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>用户类型</label>
                <select name="type" class="form-control" <?= $action == 'detail' ? 'disabled' : '' ?>>
                    <?php foreach (user_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>用户狀態</label>
                <select name="status" class="form-control" <?= $action == 'detail' ? 'disabled' : '' ?>>
                    <?php foreach (user_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('agent_id') ? 'has-error' : '' ?>">
                <label>代理名称</label>
                <input type="hidden" name="agent_id" value="<?= $row['agent_id'] ?>">
                <select name="agent_id" class="form-control" disabled>
                    <?php foreach ($agent as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= $row['agent_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('agent_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('agent_code') ? 'has-error' : '' ?>">
                <label>代理邀请码</label>
                <input type="text" id="agent_code" name="agent_code" class="form-control" placeholder="Enter ..." value="<?= $row['agent_code'] ?>" <?= $action == 'detail' ? 'disabled' : '' ?>>
                <?= form_error('agent_code', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group">
                <label>
                    注册时间: <?= isset($row['create_time']) ? $row['create_time'] : '' ?> &nbsp;&nbsp;&nbsp;
                    注册IP: <?= isset($row['create_ip']) ? $row['create_ip'] : '' ?> &nbsp;&nbsp;&nbsp;
                </label>
                <label>
                    最后登陆时间: <?= isset($row['last_login_time']) ? $row['last_login_time'] : '' ?> &nbsp;&nbsp;&nbsp;
                    最后登陆IP: <?= isset($row['last_login_ip']) ? $row['last_login_ip'] : '' ?> &nbsp;&nbsp;&nbsp;
                </label>
            </div>
            <?php if ($action != 'detail') : ?>
                <button type="submit" class="btn btn-primary">保存</button>
            <?php endif; ?>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<?php if ($this->session->userdata('roleid') == 1 && $action != 'detail'): ?>
<script>
    $('#super_user').click(function(){
        $('#agent_code').prop('readonly',$(this).prop('checked'));
    });
    <?php if ($row['super_user'] == 1): ?>
    $('#super_user').click();
    <?php endif; ?>
</script>
<?php endif; ?>