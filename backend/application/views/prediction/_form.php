<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('lottery_id') ? 'has-error' : '' ?>">
                <label>彩种</label>
                <select name="lottery_id" class="form-control">
                    <?php foreach ($lottery as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['lottery_id']) && $row['lottery_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('lottery_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('wanfa_id') ? 'has-error' : '' ?>">
                <label>玩法ID</label>
                <input type="text" name="wanfa_id" class="form-control" placeholder="Enter ..." value="<?= isset($row['wanfa_id']) ? $row['wanfa_id'] : '' ?>">
                <?= form_error('wanfa_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('ball') ? 'has-error' : '' ?>">
                <label>球号位置 <span style="color:red;">(结算用)</span></label>
                <select name="ball" class="form-control">
                    <?php foreach (prediction_model::$ballList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['ball']) && $row['ball'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('ball', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('price') ? 'has-error' : '' ?>">
                <label>价格</label>
                <input type="number" name="price" class="form-control" placeholder="Enter ..." value="<?= isset($row['price']) ? $row['price'] : '' ?>">
                <?= form_error('price', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('is_home') ? 'has-error' : '' ?>">
                <label>显示首页顺序 <span style="color:red;">(0=不显示)</span></label>
                <input type="number" name="is_home" class="form-control" placeholder="Enter ..." value="<?= isset($row['is_home']) ? $row['is_home'] : '' ?>">
                <?= form_error('is_home', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="number" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
                <?= form_error('sort', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
