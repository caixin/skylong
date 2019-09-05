<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>运营商名称</label>
                <select name="operator_id" class="form-control">
                    <?php if (!isset($row['id'])) : ?>
                        <option value="">全部</option>
                    <?php endif; ?>
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['operator_id']) && $row['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>类别</label>
                <select name="type" class="form-control">
                    <?php foreach (Customer_service_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('image_url') ? 'has-error' : '' ?>">
                <label>广告图片</label>
                <input type="text" name="image_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['image_url']) ? $row['image_url'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[image_url]\" style=\"max-width:900px;\">" : '' ?>
                <?= form_error('image_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('account') ? 'has-error' : '' ?>">
                <label>帐号</label>
                <input type="text" name="account" class="form-control" placeholder="Enter ..." value="<?= isset($row['account']) ? $row['account'] : '' ?>">
                <?= form_error('account', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->