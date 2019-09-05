<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($row['wanfa_pid']) ? $row['wanfa_pid'] : 0 ?>,<?= isset($row['wanfa_id']) ? $row['wanfa_id'] : 0 ?>">
            <div class="form-group <?= form_error('lottery_type_id') ? 'has-error' : '' ?>">
                <label>彩种类别</label>
                <select id="lottery_type_id" name="lottery_type_id" class="form-control">
                    <?php foreach ($lottery_type as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['lottery_type_id']) && $row['lottery_type_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('lottery_type_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('wanfa_pid') ? 'has-error' : '' ?>">
                <label>玩法类型</label>
                <select id="wanfa_pid" name="wanfa_pid" class="form-control">
                </select>
                <?= form_error('wanfa_pid', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('wanfa_id') ? 'has-error' : '' ?>">
                <label>玩法</label>
                <select id="wanfa_id" name="wanfa_id" class="form-control">
                </select>
                <?= form_error('wanfa_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('values') ? 'has-error' : '' ?>">
                <label>玩法值</label>
                <input type="text" name="values" class="form-control" placeholder="Enter ..." value="<?= isset($row['values']) ? $row['values'] : '' ?>">
                <?= form_error('values', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('odds') ? 'has-error' : '' ?>">
                <label>满盘賠率</label>
                <input type="number" name="odds" class="form-control" placeholder="Enter ..." value="<?= isset($row['odds']) ? $row['odds'] : '' ?>" step='0.001'>
                <?= form_error('odds', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('odds_special') ? 'has-error' : '' ?>">
                <label>特殊賠率</label>
                <input type="number" name="odds_special" class="form-control" placeholder="Enter ..." value="<?= isset($row['odds_special']) ? $row['odds_special'] : '' ?>" step='0.001'>
                <?= form_error('odds_special', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('line_a_profit') ? 'has-error' : '' ?>">
                <label>A盘获利(%)</label>
                <input type="number" name="line_a_profit" class="form-control" placeholder="Enter ..." value="<?= isset($row['line_a_profit']) ? $row['line_a_profit'] : '' ?>" step='0.001'>
                <?= form_error('line_a_profit', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('line_a_special') ? 'has-error' : '' ?>">
                <label>A盘特殊(%)</label>
                <input type="number" name="line_a_special" class="form-control" placeholder="Enter ..." value="<?= isset($row['line_a_special']) ? $row['line_a_special'] : '' ?>" step='0.001'>
                <?= form_error('line_a_special', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('qishu_max_money') ? 'has-error' : '' ?>">
                <label>单期最大限额</label>
                <input type="number" name="qishu_max_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['qishu_max_money']) ? $row['qishu_max_money'] : '' ?>">
                <?= form_error('qishu_max_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('bet_max_money') ? 'has-error' : '' ?>">
                <label>单笔最大限额</label>
                <input type="number" name="bet_max_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['bet_max_money']) ? $row['bet_max_money'] : '' ?>">
                <?= form_error('bet_max_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('bet_min_money') ? 'has-error' : '' ?>">
                <label>最小下注额</label>
                <input type="number" name="bet_min_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['bet_min_money']) ? $row['bet_min_money'] : '' ?>">
                <?= form_error('bet_min_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('formula') ? 'has-error' : '' ?>">
                <label>中奖公式</label>
                <input type="text" name="formula" class="form-control" placeholder="Enter ..." value="<?= isset($row['formula']) ? $row['formula'] : '' ?>">
                <?= form_error('formula', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('max_number') ? 'has-error' : '' ?>">
                <label>玩法值选号上限</label>
                <input type="number" name="max_number" class="form-control" placeholder="Enter ..." value="<?= isset($row['max_number']) ? $row['max_number'] : '' ?>">
                <?= form_error('max_number', '<span class="help-block">', '</span>') ?>
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
    $(function() {
        // 判斷是否有預設值
        var defaultValue = false;
        if (0 < $.trim($('#fullIdPath').val()).length) {
            $fullIdPath = $('#fullIdPath').val().split(',');
            defaultValue = true;
        }

        $('#lottery_type_id').change(function() {
            $('#wanfa_pid').empty().append("<option value=''>请选择</option>");
            $('#wanfa_id').empty().append("<option value=''>请选择</option>");
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getClassicWanfa") ?>',
                data: {
                    lottery_type_id: $('#lottery_type_id').val()
                },
                dataType: "json",
                success: function(result) {
                    for (var i = 0; i < result.length; i++) {
                        $("#wanfa_pid").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue && $fullIdPath[0] != 0) {
                        $('#wanfa_pid').val($fullIdPath[0]).change();
                    }
                    if ($('#wanfa_pid').val() == null) {
                        $('#wanfa_pid').val('');
                    }
                }
            });
        });

        $('#wanfa_pid').change(function() {
            var pid = $('#wanfa_pid').val();
            $('#wanfa_id').empty().append("<option value=''>请选择</option>");
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getClassicWanfa") ?>',
                data: {
                    lottery_type_id: $('#lottery_type_id').val(),
                    pid: pid
                },
                dataType: "json",
                success: function(result) {
                    for (var i = 0; i < result.length; i++) {
                        $("#wanfa_id").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue) {
                        $('#wanfa_id').val($fullIdPath[1]);
                    }
                    if ($('#wanfa_id').val() == null) {
                        $('#wanfa_id').val('');
                    }
                }
            });
        });
        $('#lottery_type_id').change();
    });
</script>