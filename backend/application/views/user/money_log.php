<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>营运商</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['operator_id']) && $where['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>帐户类型</label>
                <select id="money_type" name="money_type" class="form-control">
                    <?php foreach (User_model::$moneyTypeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['money_type']) && $where['money_type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>代理名称</label>
                <input type="text" name="agent_name" class="form-control" placeholder="请输入..." value="<?= isset($where['agent_name']) ? $where['agent_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户名称</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>订单号</label>
                <input type="text" name="order_sn" class="form-control" placeholder="请输入..." value="<?= isset($where['order_sn']) ? $where['order_sn'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>变动类型</label>
                <select id="type" name="type" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (User_money_log_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户类型</label>
                <select name="user_type" class="form-control">
                    <option value="">全部</option>
                    <?php foreach (User_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['user_type']) && $where['user_type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>变动时间</label>
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
                    <th><?= sort_title('id', '编号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('user_name', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('agent_id', '代理名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('order_sn', '订单号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('type', '变动类型', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money_before', '变动前余额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money_change', '变动金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money_after', '变动后余额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('description', '变动说明', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '变动时间', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_by', '操作人', $this->cur_url, $order, $where) ?></th>
                    <th>详情</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);" style="<?= $row['user_type'] == 1 ? 'color:#aaaaaa;' : '' ?>"><?= $row['user_name'] ?></a></td>
                        <td><?= $row['agent_name'] ?></td>
                        <td><?= $row['order_sn'] ?></td>
                        <td><?= User_money_log_model::$typeList[$row['type']] ?></td>
                        <td><?= $row['money_before'] ?></td>
                        <td><?= $row['money_add'] ?></td>
                        <td><?= $row['money_after'] ?></td>
                        <td><?= $row['description'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td><?= $row['create_by'] ?></td>
                        <td>
                            <?php if ($row['category'] > 0) : ?>
                                <a href="javascript:;" onclick="detail('<?= $row['url'] ?>');">【详情】</a>
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
    function detail(url) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: url,
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
</script>