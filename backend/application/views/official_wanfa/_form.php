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
            <div class="form-group <?= form_error('odds') ? 'has-error' : '' ?>">
                <label>满盘赔率</label>
                <input type="number" name="odds" class="form-control" placeholder="Enter ..." value="<?= isset($row['odds']) ? $row['odds'] : '' ?>" step="0.001">
                <?= form_error('odds', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('line_a_profit') ? 'has-error' : '' ?>">
                <label>A盘获利(%)</label>
                <input type="number" name="line_a_profit" class="form-control" placeholder="Enter ..." value="<?= isset($row['line_a_profit']) ? $row['line_a_profit'] : '' ?>" step="0.001">
                <?= form_error('line_a_profit', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('max_return') ? 'has-error' : '' ?>">
                <label>最大返点</label>
                <input type="number" name="max_return" class="form-control" placeholder="Enter ..." value="<?= isset($row['max_return']) ? $row['max_return'] : '' ?>" step="0.001">
                <?= form_error('max_return', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('max_bet_number') ? 'has-error' : '' ?>">
                <label>最大注数</label>
                <input type="number" name="max_bet_number" class="form-control" placeholder="Enter ..." value="<?= isset($row['max_bet_number']) ? $row['max_bet_number'] : '' ?>">
                <?= form_error('max_bet_number', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('max_bet_money') ? 'has-error' : '' ?>">
                <label>最大投注额</label>
                <input type="number" name="max_bet_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['max_bet_money']) ? $row['max_bet_money'] : '' ?>" step="0.001">
                <?= form_error('max_bet_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('key_word') ? 'has-error' : '' ?>">
                <label>Keyword</label>
                <input type="text" name="key_word" class="form-control" placeholder="Enter ..." value="<?= isset($row['key_word']) ? $row['key_word'] : '' ?>">
                <?= form_error('key_word', '<span class="help-block">', '</span>') ?>
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
            url: '<?= site_url("ajax/getOfficialWanfa") ?>',
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