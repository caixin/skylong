<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('type') ? 'has-error' : '' ?>">
                <label>运营商名称</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['operator_id']) && $row['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group <?= form_error('lottery_id') ? 'has-error' : '' ?>">
                <label>彩种</label>
                <select name="lottery_id" class="form-control">
                    <?php foreach ($lottery as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['lottery_id']) && $row['lottery_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('lottery_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('axis_y') ? 'has-error' : '' ?>">
                <label>Y轴上限金额</label>
                <input type="number" name="axis_y" class="form-control" placeholder="Enter ..." value="<?= isset($row['axis_y']) ? $row['axis_y'] : '' ?>">
                <?= form_error('axis_y', '<span class="help-block">', '</span>') ?>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="80">时(起始)</th>
                        <th width="80">时(结束)</th>
                        <th>最小投注總額</th>
                        <th>最大投注總額</th>
                        <th>投注總額中間值</th>
                        <th width="130">超過中間值次數</th>
                        <th width="100"><input type="button" id="total_add" class="btn btn-primary" value="添加"></th>
                    </tr>
                </thead>
                <tbody id="total_formula">
                <?php foreach ($row['total_formula'] as $key => $arr): ?>
                    <tr>
                        <td><input type="number" name="hour_start[]" class="form-control" value="<?=$arr['hour_start']?>"></td>
                        <td><input type="number" name="hour_end[]" class="form-control" value="<?=$arr['hour_end']?>"></td>
                        <td><input type="number" name="total_min[]" class="form-control" value="<?=$arr['total_min']?>"></td>
                        <td><input type="number" name="total_max[]" class="form-control" value="<?=$arr['total_max']?>"></td>
                        <td><input type="number" name="total_middle[]" class="form-control" value="<?=$arr['total_middle']?>"></td>
                        <td><input type="number" name="over_number[]" class="form-control" value="<?=$arr['over_number']?>"></td>
                        <td><input type="button" class="btn btn-primary" value="删除" onclick="$(this).parent().parent().remove();"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>下注時間(%)</th>
                        <th>下注機率(%)</th>
                        <th>最小下注金額(%)</th>
                        <th>最大下注金額(%)</th>
                        <th width="100"><input type="button" id="bet_add" class="btn btn-primary" value="添加"></th>
                    </tr>
                </thead>
                <tbody id="bet_formula">
                <?php foreach ($row['bet_formula'] as $key => $arr): ?>
                    <tr>
                        <td><input type="number" name="bet_time[]" class="form-control" value="<?=$arr['bet_time']?>" step="0.01"></td>
                        <td><input type="number" name="bet_action[]" class="form-control" value="<?=$arr['bet_action']?>" step="0.01"></td>
                        <td><input type="number" name="bet_min[]" class="form-control" value="<?=$arr['bet_min']?>"></td>
                        <td><input type="number" name="bet_max[]" class="form-control" value="<?=$arr['bet_max']?>"></td>
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
$('#total_add').click(function(){
    var addhtml = $('<tr></tr>').append(
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','hour_start[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','hour_end[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','total_min[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','total_max[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','total_middle[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','over_number[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input>").attr('type','button').addClass('btn btn-primary').attr('onClick','$(this).parent().parent().remove();').val('刪除')
        )
    );
    $("#total_formula").append(addhtml);
});
<?php if (count($row['total_formula']) == 0): ?>
$('#total_add').click();
<?php endif; ?>

$('#bet_add').click(function(){
    var addhtml = $('<tr></tr>').append(
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','bet_time[]').addClass('form-control').attr('step','0.01'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','bet_action[]').addClass('form-control').attr('step','0.01'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','bet_min[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input />").attr('type','number').attr('name','bet_max[]').addClass('form-control'),
        ),
        $('<td></td>').append(
            $("<input>").attr('type','button').addClass('btn btn-primary').attr('onClick','$(this).parent().parent().remove();').val('刪除')
        )
    );
    $("#bet_formula").append(addhtml);
});
<?php if (count($row['bet_formula']) == 0): ?>
$('#bet_add').click();
<?php endif; ?>
</script>