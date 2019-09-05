<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($where['lottery_id']) ? $where['lottery_id'] : 0 ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>玩法类别</label>
                <select id="category" name="category" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (daily_user_report_model::$categoryList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['category']) && $where['category'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>彩种</label>
                <select id="lottery_id" name="lottery_id" class="form-control">
                    <option value="">请选择</option>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户名</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户类型</label>
                <select name="type" class="form-control">
                    <?php foreach (User_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>时间</label>
                <div class="input-group" style="width:200px;">
                    <input type="text" id="day_time_from" name="day_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['day_time1']) ? $where['day_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="day_time_to" name="day_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['day_time2']) ? $where['day_time2'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>&nbsp;</label>
                <button type="submit" class="form-control btn btn-primary">查询</button>
            </div>
        </form>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= sort_title('uid', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('category', '玩法类别', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_number', '笔数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_money', '投注总额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('c_value', '派奖金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_eff', '有效投注额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('profit', '平台输赢', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);" style="<?= $row['user_type'] == 1 ? 'color:#aaaaaa;' : '' ?>"><?= $row['user_name'] ?></a></td>
                        <td><?= daily_user_report_model::$categoryList[$row['category']] ?></td>
                        <td><?= $row['bet_number'] ?></td>
                        <td><?= $row['bet_money'] ?></td>
                        <td><?= $row['c_value'] ?></td>
                        <td><?= $row['bet_eff'] ?></td>
                        <td style="color:<?=base_model::getProfitColor($row['profit'])?>"><?= $row['profit'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="color:blue;font-weight:bold;">
                    <td>总计</td>
                    <td></td>
                    <td><?= $footer['bet_number'] ?></td>
                    <td><?= $footer['bet_money'] ?></td>
                    <td><?= $footer['c_value'] ?></td>
                    <td><?= $footer['bet_eff'] ?></td>
                    <td style="color:<?=base_model::getProfitColor($footer['profit'])?>"><?= $footer['profit'] ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<script>
    $(function() {
        // 判斷是否有預設值
        var defaultValue = false;
        if (0 < $.trim($('#fullIdPath').val()).length) {
            $fullIdPath = $('#fullIdPath').val().split(',');
            defaultValue = true;
        }

        $('#category').change(function() {
            $('#lottery_id').empty().append("<option value=''>请选择</option>");
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getLottery") ?>',
                data: {
                    category: $('#category').val()
                },
                dataType: "json",
                success: function(result) {
                    for (var i = 0; i < result.length; i++) {
                        $("#lottery_id").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue && $fullIdPath[0] != 0) {
                        $('#lottery_id').val($fullIdPath[0]);
                    }
                }
            });
        });
        $('#category').change();
    });
    //用戶詳情
    function user_detail(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("user/detail") ?>/' + id
        });
    }
</script>