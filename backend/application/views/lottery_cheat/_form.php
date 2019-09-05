<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
        <?php if ($action == 'create'): ?>
            <div class="form-group <?= form_error('lottery_id') ? 'has-error' : '' ?>">
                <label>彩种名称</label>
                <select name="lottery_id" class="form-control">
                    <?php foreach ($lottery as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['lottery_id']) && $row['lottery_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('lottery_id', '<span class="help-block">', '</span>') ?>
            </div>
        <?php endif; ?>
        <?php if ($type == 2): ?>
            <div class="form-group <?= form_error('qishu') ? 'has-error' : '' ?>">
                <label>期数</label>
                <input type="text" name="qishu" class="form-control" placeholder="Enter ..." value="<?= isset($row['qishu']) ? $row['qishu'] : '' ?>">
                <?= form_error('qishu', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('numbers') ? 'has-error' : '' ?>">
                <label>号码</label>
                <input type="text" name="numbers" class="form-control" placeholder="Enter ..." value="<?= isset($row['numbers']) ? $row['numbers'] : '' ?>">
                <?= form_error('numbers', '<span class="help-block">', '</span>') ?>
            </div>
        <?php endif; ?>
        <?php if ($type == 3): ?>
            <div class="form-group <?= form_error('starttime') ? 'has-error' : '' ?>">
                <label>起始时间</label>
                <input type="text" name="starttime" class="form-control timepicker" placeholder="Enter ..." value="<?= isset($row['starttime']) ? $row['starttime'] : '' ?>">
                <?= form_error('starttime', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('endtime') ? 'has-error' : '' ?>">
                <label>结束时间</label>
                <input type="text" name="endtime" class="form-control timepicker" placeholder="Enter ..." value="<?= isset($row['endtime']) ? $row['endtime'] : '' ?>">
                <?= form_error('endtime', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('percent') ? 'has-error' : '' ?>">
                <label>百分比</label>
                <input type="number" name="percent" class="form-control" placeholder="Enter ..." value="<?= isset($row['percent']) ? $row['percent'] : '' ?>">
                <?= form_error('percent', '<span class="help-block">', '</span>') ?>
            </div>
        <?php endif; ?>
        <?php if ($type != 2): ?>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (Ettm_lottery_cheat_model::${$type == 0 ? "status0List":"statusList"} as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
        <?php endif; ?>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->