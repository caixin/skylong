<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group">
                <label>模組名称： <span style="color:blue;"><?= isset($row['name']) ? $row['name'] : '' ?></span></label>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (module_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>參數Key</th>
                        <th>參數值</th>
                        <th width="100"><input type="button" id="param_add" class="btn btn-primary" value="添加"></th>
                    </tr>
                </thead>
                <tbody id="param">
                <?php foreach ($row['param'] as $key => $val): ?>
                    <tr>
                        <td><input type="text" name="param_key[]" class="form-control" value="<?=$key?>"></td>
                        <td><input type="text" name="param_val[]" class="form-control" value="<?=$val?>"></td>
                        <td><input type="button" class="btn btn-primary" value="删除" onclick="$(this).parent().parent().remove();"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<script>
$('#param_add').click(function(){
    var addhtml = $('<tr></tr>').append(
        $('<td></td>').append(
            $("<input />").attr('type','text').attr('name','param_key[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','text').attr('name','param_val[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input>").attr('type','button').addClass('btn btn-primary').attr('onClick','$(this).parent().parent().remove();').val('刪除')
        )
    );
    $("#param").append(addhtml);
});
<?php if (count($row['param']) == 0): ?>
$('#param_add').click();
<?php endif; ?>
</script>