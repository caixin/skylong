<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('operator_id') ? 'has-error' : '' ?>">
                <label>选择运营</label>
                <select name="operator_id" class="form-control">
                    <?php foreach (['0'=>'共用变量','1'=>'各運營变量'] as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($new['operator_id']) && $new['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('operator_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('groupid') ? 'has-error' : '' ?>">
                <label>变量组</label>
                <select name="groupid" class="form-control">
                    <?php foreach (sysconfig_model::$groupList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($new['groupid']) && $new['groupid'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('groupid', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>变量类型</label>
                <select name="type" class="form-control">
                    <?php foreach (sysconfig_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($new['type']) && $new['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('varname') ? 'has-error' : '' ?>">
                <label>变量名称</label>
                <input type="text" name="varname" class="form-control" placeholder="请输入..." value="<?= isset($new['varname']) ? $new['varname'] : '' ?>">
                <?= form_error('varname', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('value') ? 'has-error' : '' ?>">
                <label>变量值</label>
                <input type="text" name="value" class="form-control" placeholder="请输入..." value="<?= isset($new['value']) ? $new['value'] : '' ?>">
                <?= form_error('value', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('info') ? 'has-error' : '' ?>">
                <label>变量说明</label>
                <input type="text" name="info" class="form-control" placeholder="请输入..." value="<?= isset($new['info']) ? $new['info'] : '' ?>">
                <?= form_error('info', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('number') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="number" name="sort" class="form-control" placeholder="请输入..." value="<?= isset($new['sort']) ? $new['sort'] : 0 ?>">
                <?= form_error('number', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->