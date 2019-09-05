<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($row['lottery_id']) ? $row['lottery_id'] : 0 ?>">
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>运营商名称</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['operator_id']) && $row['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group <?= form_error('lottery_type_id') ? 'has-error' : '' ?>">
                <label>彩种大类</label>
                <select id="lottery_type_id" name="lottery_type_id" class="form-control">
                <?php foreach ([0=>'全部']+$lottery_type as $key => $val) : ?>
                    <option value="<?= $key ?>" <?= isset($row['lottery_type_id']) && $row['lottery_type_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                <?php endforeach; ?>
                </select>
                <?= form_error('lottery_type_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('lottery_id') ? 'has-error' : '' ?>">
                <label>彩种</label>
                <select id="lottery_id" name="lottery_id" class="form-control">
                    <option value="0">全部</option>
                </select>
                <?= form_error('lottery_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>类型</label>
                <select name="type" class="form-control">
                    <?php foreach (ettm_reduce_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>區間</th>
                        <th>降賠%數</th>
                        <th>執行次數</th>
                        <th width="100"><input type="button" id="item_add" class="btn btn-primary" value="添加"></th>
                    </tr>
                </thead>
                <tbody id="items">
                <?php foreach ($row['items'] as $key => $arr): ?>
                    <tr>
                        <td><input type="number" name="interval[]" class="form-control" value="<?= isset($arr['interval']) ? $arr['interval'] : '' ?>"></td>
                        <td><input type="number" name="value[]" class="form-control" value="<?= isset($arr['value']) ? $arr['value'] : '' ?>" step="0.01"></td>
                        <td><input type="number" name="count[]" class="form-control" value="<?= isset($arr['count']) ? $arr['count'] : '' ?>"></td>
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
$(function() {
    // 判斷是否有預設值
    var defaultValue = false;
    if (0 < $.trim($('#fullIdPath').val()).length) {
        $fullIdPath = $('#fullIdPath').val().split(',');
        defaultValue = true;
    }

    $('#lottery_type_id').change(function() {
        $('#lottery_id').empty().append("<option value='0'>全部</option>");;
        $.ajax({
            type: "POST",
            url: '<?= site_url("ajax/getLottery") ?>',
            data: {
                category: 1,
                typeid: $('#lottery_type_id').val()
            },
            dataType: "json",
            success: function(result) {
                for (var i = 0; i < result.length; i++) {
                    $("#lottery_id").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                }
                // 設定預設選項
                if (defaultValue) {
                    $('#lottery_id').val($fullIdPath[0]);
                }
                if ($('#lottery_id').val() == null) {
                    $('#lottery_id').val('');
                }
            }
        });
    });
    $('#lottery_type_id').change();
});

$('#item_add').click(function(){
    var addhtml = $('<tr></tr>').append(
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','interval[]').addClass('form-control').val(1000).attr('step','1')
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','value[]').addClass('form-control').val(1).attr('step','0.01')
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','count[]').addClass('form-control').val(1).attr('step','1')
        ),
        $('<td></td>').append(
            $("<input>").attr('type','button').addClass('btn btn-primary').attr('onClick','$(this).parent().parent().remove();').val('刪除')
        )
    );
    $("#items").append(addhtml);
});
<?php if (count($row['items']) == 0): ?>
$('#item_add').click();
<?php endif; ?>
</script>