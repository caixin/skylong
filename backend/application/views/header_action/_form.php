<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('mode') ? 'has-error' : '' ?>">
                <label>模式</label>
                <div class="checkbox">
                    <input type="hidden" name="mode[]" value="0">
                    <?php foreach (header_action_model::$modeList as $key => $val) : ?>
                        <label><input type="checkbox" name="mode[]" value="<?= $key ?>" <?= isset($row['mode']) && in_array($key, $row['mode']) ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </div>
                <?= form_error('mode', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('title') ? 'has-error' : '' ?>">
                <label>标题</label>
                <input type="text" name="title" class="form-control" placeholder="Enter ..." value="<?= isset($row['title']) ? $row['title'] : '' ?>">
                <?= form_error('title', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('icon') ? 'has-error' : '' ?>">
                <label>图片</label>
                <input type="text" name="icon" class="form-control" placeholder="Enter ..." value="<?= isset($row['icon']) ? $row['icon'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[icon]\" style=\"max-width:900px;\">" : '' ?>
                <?= form_error('icon', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('jump_url') ? 'has-error' : '' ?>">
                <label>跳转地址</label>
                <input type="text" name="jump_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['jump_url']) ? $row['jump_url'] : '' ?>">
                <?= form_error('jump_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (Header_action_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->