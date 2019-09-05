<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($where['lottery_id']) ? $where['lottery_id'] : 0 ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>代理</label>
                <select name="agent_id" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach ($agent as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['agent_id']) && $where['agent_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>统计时间</label>
                <div class="input-group">
                    <input type="text" id="starttime_from" name="starttime" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['starttime']) ? $where['starttime'] : '' ?>" autocomplete="off">
                    <input type="text" id="endtime_to" name="endtime" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['endtime']) ? $where['endtime'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>&nbsp;</label>
                <button type="submit" class="form-control btn btn-primary">查询</button>
            </div>
        </form>
    </div>
    <div class="box-header">
        <label for="per_page">显示笔数:</label>
        <input type="test" id="per_page" value="<?= $this->per_page ?>" size="1">
        <h5 class="box-title" style="font-size: 14px;"><b>总计:</b> <?= $total ?></h5>
        <?= $this->pagination->create_links() ?>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= sort_title('agent_id', '代理编号', $this->cur_url, $order, $where) ?></th>
                    <th>代理帐号</th>
                    <th><?= sort_title('code', '	邀请码', $this->cur_url, $order, $where) ?></th>
                    <th>线下会员</th>
                    <th>充值</th>
                    <th>提现</th>
                    <th>彩金</th>
                    <th>返水</th>
                    <th>输赢</th>
                    <th>代理返点</th>
                    <th>打码量</th>
                    <th>返点设置</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['agent_id'] ?></td>
                        <td><?= $agent[$row['agent_id']] ?></td>
                        <td><?= $row['code'] ?></td>
                        <td><a href="javascript:sub_user('<?= $row['code'] ?>')"><?= $row['user_number'] ?></a></td>
                        <td><?= $row['recharge_money'] ?></td>
                        <td><?= $row['withdraw_money'] ?></td>
                        <td><?= $row['bonus_money'] ?></td>
                        <td><?= $row['rakeback_money'] ?></td>
                        <td style="color:<?=base_model::getProfitColor($row['profit'])?>"><?= $row['profit'] ?></td>
                        <td><?= $row['return_point'] ?></td>
                        <td><?= $row['code_amount'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/code_detail", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="detail('<?= $row['code'] ?>')">查询</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
        <?= $this->pagination->create_links() ?>
    </div>
</div>
<script>
    //下級會員
    function sub_user(code) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: '<?= site_url("{$this->router->class}/sub_user/$where[starttime]/$where[endtime]/agent_code") ?>/' + code
        });
    }
    //詳情
    function detail(code) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: '<?= site_url("{$this->router->class}/code_detail") ?>/' + code
        });
    }

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
</script>