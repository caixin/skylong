<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($row['pid']) ? $row['pid'] : 0 ?>" />
            <div class="form-group <?= form_error('lottery_type_id') ? 'has-error' : '' ?>">
                <label>彩种类别</label>
                <select id="lottery_type_id" name="lottery_type_id" class="form-control">
                    <?php foreach ($lottery_type as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['lottery_type_id']) && $row['lottery_type_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('lottery_type_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('pid') ? 'has-error' : '' ?>">
                <label>上层玩法</label>
                <select id="pid" name="pid" class="form-control">
                    <option value="0">无上层</option>
                </select>
                <?= form_error('pid', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>玩法名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
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
<script>
    $('#lottery_type_id').change(function() {
        // 判斷是否有預設值
        var defaultValue = false;
        if (0 < $.trim($('#fullIdPath').val()).length) {
            $fullIdPath = $('#fullIdPath').val().split(',');
            defaultValue = true;
        }
        $('#pid').empty().append("<option value='0'>无上层</option>");
        $.ajax({
            type: "POST",
            url: '<?= site_url("ajax/getClassicWanfa") ?>',
            data: {
                lottery_type_id: $('#lottery_type_id').val()
            },
            dataType: "json",
            success: function(result) {
                for (var i = 0; i < result.length; i++) {
                    $("#pid").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                }
                // 設定預設選項
                if (defaultValue) {
                    $('#pid').val($fullIdPath[0]);
                }
                if ($('#pid').val() == null) {
                    $('#pid').val(0);
                }
            }
        });
    });
    $('#lottery_type_id').change();
</script>