<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>时间</label>
                <div class="input-group" style="width:auto;">
                    <input type="text" id="create_time1" name="create_time1" class="form-control datepicker" placeholder="时间" value="<?= isset($where['create_time1']) ? $where['create_time1'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户名称</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>手机号</label>
                <input type="text" name="mobile" class="form-control" placeholder="请输入..." value="<?= isset($where['mobile']) ? $where['mobile'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>渠道</label>
                <select name="type" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (recharge_order_model::$typeList as $key => $val) : ?>
                    <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>充值方式</label>
                <select name="channel" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (recharge_offline_model::$channelList as $key => $val) : ?>
                    <option value="<?= $key ?>" <?= isset($where['channel']) && $where['channel'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
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
                    <th><?= sort_title('user_name', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('real_name', '姓名', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('mobile', '手机号码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('type', '渠道', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('offline_channel', '充值方式', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('grand_total', '充值总数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('today_total', '当日充值总数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money', '充值金额', $this->cur_url, $order, $where) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                <tr>
                    <td><?= $row['user_name'] ?></td>
                    <td><?= $row['real_name'] ?></td>
                    <td><?= $row['mobile'] ?></td>
                    <td><?= recharge_order_model::$typeList[$row['type']] ?></td>
                    <td><?= recharge_offline_model::$channelList[$row['offline_channel']] ?></td>
                    <td><?= $row['grand_total'] ?></td>
                    <td><?= $row['today_total'] ?></td>
                    <td><?= $row['money'] ?></td>
                    <td><?= $row['first_recharge'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="color:blue;font-weight:bold;">
                    <td>总计</td>
                    <td><?= $footer['count'] ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?= $footer['money'] ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
        <?= $this->pagination->create_links() ?>
    </div>
</div>