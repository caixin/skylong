<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('domain_url') ? 'has-error' : '' ?>">
                <label>网域名称</label>
                <input type="text" name="domain_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['domain_url']) ? $row['domain_url'] : '' ?>">
                <?= form_error('domain_url', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('cnzz_url') ? 'has-error' : '' ?>">
                <label>链接</label>
                <input type="text" name="cnzz_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['cnzz_url']) ? $row['cnzz_url'] : '' ?>">
                <?= form_error('cnzz_url', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->