<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
            <input type="hidden" name="recharge_order_id" value="<?= isset($where['recharge_order_id']) ? $where['recharge_order_id'] : '' ?>">
            <input type="hidden" name="prediction_relief_id" value="<?= isset($where['prediction_relief_id']) ? $where['prediction_relief_id'] : '' ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>创建时间</label>
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
                    <th><?= sort_title('uid', '用户名', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_id', '彩种', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('qishu', '期数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('name', '位置', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('order_sn', '充值单号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money', '充值金額', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_money', '需要充值金額', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('reacharge_use', '分配金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '创建时间', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['user_name'] ?></td>
                        <td><?= $lottery[$row['lottery_id']] ?></td>
                        <td><?= $row['qishu'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['order_sn'] ?></td>
                        <td><?= $row['money'] ?></td>
                        <td><?= $row['bet_money'] ?></td>
                        <td><?= $row['reacharge_use'] ?></td>
                        <td><?= $row['create_time'] ?></td>
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