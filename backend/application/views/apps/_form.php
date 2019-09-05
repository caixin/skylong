<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group">
                <label>运营商名称</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['operator_id']) && $row['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>应用类型</label>
                <select name="type" class="form-control">
                    <?php foreach (apps_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>应用名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('jump_url') ? 'has-error' : '' ?>">
                <label>跳转URL(H5网页地址)</label>
                <input type="text" name="jump_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['jump_url']) ? $row['jump_url'] : '' ?>">
                <?= form_error('jump_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('download_url') ? 'has-error' : '' ?>">
                <label>下载URL</label>
                <input type="text" name="download_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['download_url']) ? $row['download_url'] : '' ?>">
                <?= form_error('download_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group">
                <label>是否为VIP包</label>
                <select name="is_vip" class="form-control">
                    <?php foreach (apps_model::$is_vipList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['is_vip']) && $row['is_vip'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (apps_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->