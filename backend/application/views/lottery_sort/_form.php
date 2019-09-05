<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group">
                <label>彩种名称： <span style="color:blue;"><?= isset($row['name']) ? $row['name'] : '' ?></span></label>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="text" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
                <?= form_error('sort', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (Ettm_lottery_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('is_hot') ? 'has-error' : '' ?>">
                <label>是否为热门彩种</label>
                <div class="radio">
                    <?php foreach (Ettm_lottery_model::$is_hotList as $key => $val) : ?>
                        <label><input type="radio" name="is_hot" value="<?= $key ?>" <?= isset($row['is_hot']) && $row['is_hot'] == $key ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </div>
                <?= form_error('is_hot', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('hot_logo') ? 'has-error' : '' ?>">
                <label>是否有HOT的Logo <span style="color:red;">【PC热门彩种专用】</span></label>
                <div class="radio">
                    <?php foreach (Ettm_lottery_model::$hot_logoList as $key => $val) : ?>
                        <label><input type="radio" name="hot_logo" value="<?= $key ?>" <?= isset($row['hot_logo']) && $row['hot_logo'] == $key ? 'checked' : '' ?>> <?= $val ?></label>
                    <?php endforeach; ?>
                </div>
                <?= form_error('hot_logo', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->