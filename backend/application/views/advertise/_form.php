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
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>广告名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>广告位置</label>
                <select name="type" class="form-control">
                    <?php foreach (Advertise_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pic') ? 'has-error' : '' ?>">
                <label>广告图片</label>
                <input type="text" name="pic" class="form-control" placeholder="Enter ..." value="<?= isset($row['pic']) ? $row['pic'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[pic]\" style=\"max-width:900px;\">" : '' ?>
                <?= form_error('pic', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pic_url') ? 'has-error' : '' ?>">
                <label>广告图片指向地址</label>
                <input type="text" name="pic_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['pic_url']) ? $row['pic_url'] : '' ?>">
                <?= form_error('pic_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('key_word') ? 'has-error' : '' ?>">
                <label>key_word</label>
                <input type="text" name="key_word" class="form-control" placeholder="Enter ..." value="<?= isset($row['key_word']) ? $row['key_word'] : '' ?>">
                <?= form_error('key_word', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序 <span style="color:red;">【数字大者顺序在前】</span></label>
                <input type="text" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
                <?= form_error('sort', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (Advertise_model::$statusList as $key => $val) : ?>
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