<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($row['lottery_type_id']) ? $row['lottery_type_id'] : 0 ?>,<?= isset($row['lottery_id']) ? $row['lottery_id'] : 0 ?>">
            <div class="form-group <?= form_error('operator_id') ? 'has-error' : '' ?>">
                <label>运营商</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['operator_id']) && $row['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('operator_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('user_group_id') ? 'has-error' : '' ?>">
                <label>会员分层</label>
                <select name="user_group_id" class="form-control">
                    <?php foreach ($user_group as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['user_group_id']) && $row['user_group_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('user_group_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>返水类型</label>
                <select name="type" class="form-control">
                    <?php foreach (user_rakeback_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['type']) && $row['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('type', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('category') ? 'has-error' : '' ?>">
                <label>玩法类别</label>
                <select id="category" name="category" class="form-control">
                    <?php foreach (user_rakeback_model::$categoryList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['category']) && $row['category'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('category', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('lottery_type_id') ? 'has-error' : '' ?>">
                <label>彩种大类</label>
                <select id="lottery_type_id" name="lottery_type_id" class="form-control"></select>
                <?= form_error('lottery_type_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('lottery_id') ? 'has-error' : '' ?>">
                <label>彩种</label>
                <select id="lottery_id" name="lottery_id" class="form-control"></select>
                <?= form_error('lottery_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('start_money') ? 'has-error' : '' ?>">
                <label>起算金額</label>
                <input type="text" name="start_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['start_money']) ? $row['start_money'] : '' ?>">
                <?= form_error('start_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('rakeback_per') ? 'has-error' : '' ?>">
                <label>返水比率</label>
                <input type="number" name="rakeback_per" class="form-control" placeholder="Enter ..." value="<?= isset($row['rakeback_per']) ? $row['rakeback_per'] : '' ?>" step="0.01">
                <?= form_error('rakeback_per', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('rakeback_max') ? 'has-error' : '' ?>">
                <label>返水上限</label>
                <input type="number" name="rakeback_max" class="form-control" placeholder="Enter ..." value="<?= isset($row['rakeback_max']) ? $row['rakeback_max'] : '' ?>">
                <?= form_error('rakeback_max', '<span class="help-block">', '</span>') ?>
            </div>
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

        $('#category').change(function() {
            $('#lottery_type_id').empty().append("<option value='0'>全部</option>");;
            $('#lottery_id').empty().append("<option value='0'>全部</option>");;
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getLotteryType") ?>',
                data: {
                    category: $('#category').val()
                },
                dataType: "json",
                success: function(result) {
                    for (var i = 0; i < result.length; i++) {
                        $("#lottery_type_id").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue && $fullIdPath[0] != 0) {
                        $('#lottery_type_id').val($fullIdPath[0]).change();
                    }
                    if ($('#lottery_type_id').val() == null) {
                        $('#lottery_type_id').val('');
                    }
                }
            });
        });

        $('#lottery_type_id').change(function() {
            $('#lottery_id').empty().append("<option value='0'>全部</option>");;
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getLottery") ?>',
                data: {
                    category: $('#category').val(),
                    typeid: $('#lottery_type_id').val()
                },
                dataType: "json",
                success: function(result) {
                    for (var i = 0; i < result.length; i++) {
                        $("#lottery_id").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue) {
                        $('#lottery_id').val($fullIdPath[1]);
                    }
                    if ($('#lottery_id').val() == null) {
                        $('#lottery_id').val('');
                    }
                }
            });
        });
        $('#category').change();
    });
</script>