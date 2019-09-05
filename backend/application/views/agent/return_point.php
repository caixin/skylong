<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($where['lottery_id']) ? $where['lottery_id'] : 0 ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>用户名</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>玩法类别</label>
                <select id="category" name="category" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (agent_return_point_model::$categoryList as $key => $val) : ?>
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
                <label>期数</label>
                <input type="text" name="qishu" class="form-control" placeholder="请输入..." value="<?= isset($where['qishu']) ? $where['qishu'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>返点时间</label>
                <div class="input-group">
                    <input type="text" id="create_time_from" name="create_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['create_time1']) ? $where['create_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="create_time_to" name="create_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['create_time2']) ? $where['create_time2'] : '' ?>" autocomplete="off">
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
                    <th><?= sort_title('uid', '用户名', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('category', '玩法类别', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_id', '彩种', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('qishu', '期数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('amount', '返点值', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '返点时间', $this->cur_url, $order, $where) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);" style="<?=$row['user_type'] == 1 ? 'color:#aaaaaa;':''?>"><?= $row['user_name'] ?></a></td>
                        <td><?= agent_return_point_model::$categoryList[$row['category']] ?></td>
                        <td><?= $lottery[$row['lottery_id']] ?></td>
                        <td><?= $row['qishu'] ?></td>
                        <td><?= $row['amount'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/detail", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="detail(<?= $row['uid'] ?>,<?= $row['category'] ?>,<?= $row['lottery_id'] ?>,<?= $row['qishu'] ?>)">详情</button>
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
    //詳情
    function detail(uid, category, lottery_id, qishu) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['60%', '90%'],
            content: '<?= site_url("{$this->router->class}/return_point_detail") ?>/' + uid + '/' + category + '/' + lottery_id + '/' + qishu
        });
    }
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