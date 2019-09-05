<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>类别名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('key_word') ? 'has-error' : '' ?>">
                <label>Keyword</label>
                <input type="text" name="key_word" class="form-control" placeholder="Enter ..." value="<?= isset($row['key_word']) ? $row['key_word'] : '' ?>">
                <?= form_error('key_word', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pic_icon') ? 'has-error' : '' ?>">
                <label>图片URL</label>
                <input type="text" name="pic_icon" class="form-control" placeholder="Enter ..." value="<?= isset($row['pic_icon']) ? $row['pic_icon'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[pic_icon]\">" : '' ?>
                <?= form_error('pic_icon', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('mode') ? 'has-error' : '' ?>">
                <label>玩法模式</label>
                <div class="checkbox">
                    <input type="hidden" name="mode[]" value="0">
                    <?php foreach (Ettm_lottery_type_model::$modeList as $key => $val) : ?>
                        <label><input type="checkbox" name="mode[]" value="<?= $key ?>" <?= isset($row['mode']) && in_array($key, $row['mode']) ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </div>
                <?= form_error('mode', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="text" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
                <?= form_error('sort', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->