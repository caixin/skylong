<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>运营商名称</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['operator_id']) && $row['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="number" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
                <?= form_error('sort', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>文章类型</label>
                <select name="type" class="form-control">
                    <?php foreach (news_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('lottery_id') ? 'has-error' : '' ?>">
                <label>彩種</label>
                <select name="lottery_id" class="form-control">
                    <option value="0">无</option>
                    <?php foreach ($lottery as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['lottery_id']) && $row['lottery_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('lottery_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('title') ? 'has-error' : '' ?>">
                <label>标题</label>
                <input type="text" name="title" class="form-control" placeholder="Enter ..." value="<?= isset($row['title']) ? $row['title'] : '' ?>">
                <?= form_error('title', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('content_wap') ? 'has-error' : '' ?>">
                <label>Wap文章内容</label>
                <textarea id="content_wap" name="content_wap" class="form-control" placeholder="Enter ..."><?= isset($row['content_wap']) ? $row['content_wap'] : '' ?></textarea>
                <?= form_error('content_wap', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('content_pc') ? 'has-error' : '' ?>">
                <label>Pc文章内容</label>
                <textarea id="content_pc" name="content_pc" class="form-control" placeholder="Enter ..."><?= isset($row['content_pc']) ? $row['content_pc'] : '' ?></textarea>
                <?= form_error('content_pc', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (news_model::$statusList as $key => $val) : ?>
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
<script src="<?= base_url('static/bower_components/ckeditor/ckeditor.js') ?>"></script>
<script>
    $(function() {
        CKEDITOR.replace('content_wap')
        CKEDITOR.replace('content_pc')
    })
</script>