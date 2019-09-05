<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>运营商名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('domain_url') ? 'has-error' : '' ?>">
                <label>绑定网域</label>
                <select name="domain_url[]" class="form-control select2" multiple="multiple">
                    <?php foreach ($row['domain_url'] as $val) : ?>
                        <option selected><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('domain_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('classic_adjustment') ? 'has-error' : '' ?>">
                <label>经典A盘调整 <span style="color:red;">【倍数】</span></label>
                <input type="number" name="classic_adjustment" class="form-control" value="<?= isset($row['classic_adjustment']) ? $row['classic_adjustment'] : '' ?>" min="0" step="0.001">
                <?= form_error('classic_adjustment', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('official_adjustment') ? 'has-error' : '' ?>">
                <label>官方A盘调整 <span style="color:red;">【倍数】</span></label>
                <input type="number" name="official_adjustment" class="form-control" value="<?= isset($row['official_adjustment']) ? $row['official_adjustment'] : '' ?>" min="0" step="0.001">
                <?= form_error('official_adjustment', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<script>
    $('.select2').select2({
        tags: true,
        tokenSeparators: [",", " "]
    });
</script>