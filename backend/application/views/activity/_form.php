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
                <label>活动类型</label>
                <select name="type" class="form-control">
                    <?php foreach (activity_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>活动标题</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="text" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
                <?= form_error('sort', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pic1') ? 'has-error' : '' ?>">
                <label>首页轮播 &nbsp;
                    <?php foreach (activity_model::$pic1_showList as $key => $val) : ?>
                        <label style="color:blue;"><input type="radio" name="pic1_show" value="<?= $key ?>" <?= isset($row['pic1_show']) && $row['pic1_show'] == $key ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </label>
                <input type="text" name="pic1" class="form-control" placeholder="Enter ..." value="<?= isset($row['pic1']) ? $row['pic1'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[pic1]\" style=\"max-width:500px;\">" : '' ?>
                <?= form_error('pic1', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pic2') ? 'has-error' : '' ?>">
                <label>上传活动图(模板1) &nbsp;
                    <?php foreach (activity_model::$pic2_showList as $key => $val) : ?>
                        <label style="color:blue;"><input type="radio" name="pic2_show" value="<?= $key ?>" <?= isset($row['pic2_show']) && $row['pic2_show'] == $key ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </label>
                <input type="text" name="pic2" class="form-control" placeholder="Enter ..." value="<?= isset($row['pic2']) ? $row['pic2'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[pic2]\" style=\"max-width:500px;\">" : '' ?>
                <?= form_error('pic2', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pic3') ? 'has-error' : '' ?>">
                <label>上传活动图(模板2) &nbsp;
                    <?php foreach (activity_model::$pic3_showList as $key => $val) : ?>
                        <label style="color:blue;"><input type="radio" name="pic3_show" value="<?= $key ?>" <?= isset($row['pic3_show']) && $row['pic3_show'] == $key ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </label>
                <input type="text" name="pic3" class="form-control" placeholder="Enter ..." value="<?= isset($row['pic3']) ? $row['pic3'] : '' ?>">
                <?= $action == 'edit' ? "<img src=\"$row[pic3]\" style=\"max-width:500px;\">" : '' ?>
                <?= form_error('pic3', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('content') ? 'has-error' : '' ?>">
                <label>活动内容</label>
                <textarea id="content" name="content" class="form-control" placeholder="Enter ..."><?= isset($row['content']) ? $row['content'] : '' ?></textarea>
                <?= form_error('content', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (Activity_model::$statusList as $key => $val) : ?>
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
        CKEDITOR.replace('content')
    })
</script>