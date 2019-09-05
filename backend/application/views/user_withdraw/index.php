<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>用户名称</label>
                <input type="text" name="username" class="form-control" placeholder="请输入..." value="<?= isset($where['username']) ? $where['username'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>订单号</label>
                <input type="text" name="order_sn" class="form-control" placeholder="请输入..." value="<?= isset($where['order_sn']) ? $where['order_sn'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>狀態</label>
                <select name="status" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (user_withdraw_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['status']) && $where['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>申请时间</label>
                <div class="input-group" style="width:200px;">
                    <input type="text" id="create_time_from" name="create_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['create_time1']) ? $where['create_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="create_time_to" name="create_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['create_time2']) ? $where['create_time2'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>处理时间</label>
                <div class="input-group" style="width:200px;">
                    <input type="text" id="check_time_from" name="check_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['check_time1']) ? $where['check_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="check_time_to" name="check_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['check_time2']) ? $where['check_time2'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1">
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
                    <th><?= sort_title('uid', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('order_sn', '订单号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bank_realname', '银行卡户名', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bank_name', '收款银行', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bank_account', '银行卡账号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money', '提现金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('grand_total', '提现总数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('today_total', '当日提现数', $this->cur_url, $order, $where) ?></th>
                    <th width="100"><?= sort_title('create_time', '申请时间', $this->cur_url, $order, $where) ?></th>
                    <th width="100"><?= sort_title('check_time', '处理时间', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('check_remarks', '处理备注', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('status', '状态', $this->cur_url, $order, $where) ?></th>
                    <th>快捷查询</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);" style="<?= $row['user_type'] == 1 ? 'color:#aaaaaa;' : '' ?>"><?= $row['user_name'] ?></a></td>
                        <td><?= $row['order_sn'] ?></td>
                        <td><?= $row['bank_realname'] ?></td>
                        <td><?= $row['bank_name'] ?></td>
                        <td><?= $row['bank_account'] ?></td>
                        <td><?= $row['money'] ?></td>
                        <td><?= $row['grand_total'] ?></td>
                        <td><?= $row['today_total'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td><?= $row['check_time'] ?></td>
                        <td><?= $row['check_remarks'] ?></td>
                        <td style="color:<?= user_withdraw_model::$statusColorList[$row['status']] ?>;"><?= user_withdraw_model::$statusList[$row['status']] ?></td>
                        <td>
                            <a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);">【会员资料】</a>
                            <a href="javascript:;" onclick="user_money_log('<?= $row['user_name'] ?>');">【账变明细】</a><br>
                            <a href="javascript:;" onclick="code_amount('<?= $row['user_name'] ?>');">【打码量稽核】</a>
                        </td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/check", $this->allow_url)) : ?>
                                <?php if ($row['status'] == 0) : ?>
                                    <button type="button" class="btn btn-primary" onclick="check(<?= $row['id'] ?>)">审核</button>
                                <?php else : ?>
                                    <?= $row['check_by'] ?>
                                <?php endif; ?>
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
    //编辑
    function check(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/check") ?>/' + id
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
    //用戶帳變明細
    function user_money_log(name) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: '<?= site_url("user/money_log/sidebar/0/user_name") ?>/' + name
        });
    }
    //打碼量稽核
    function code_amount(name) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: '<?= site_url("code_amount/check/sidebar/0/user_name") ?>/' + name
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