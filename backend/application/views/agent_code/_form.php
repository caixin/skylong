<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>类型</label>
                <select name="type" class="form-control">
                    <?php foreach (agent_code_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group <?= form_error('note') ? 'has-error' : '' ?>">
                <label>备注</label>
                <input type="text" name="note" class="form-control" placeholder="Enter ..." value="<?= isset($row['note']) ? $row['note'] : '' ?>">
                <?= form_error('note', '<span class="help-block">', '</span>') ?>
            </div>
            <?php foreach ($row['detail'] as $id => $return_point): ?>
            <div class="form-group">
                <label><?=$lottery[$id]?></label>
                <input type="text" name="detail[<?=$id?>]" class="form-control" placeholder="Enter ..." value="<?=$return_point?>">
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->