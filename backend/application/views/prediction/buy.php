<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>彩种ID</label>
                <select name="lottery_id" class="form-control">
                    <option value="">全部</option>
                    <?php foreach ($lottery as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['lottery_id']) && $where['lottery_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>期数</label>
                <input type="text" name="qishu" class="form-control" placeholder="请输入..." value="<?= isset($where['qishu']) ? $where['qishu'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户名</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>预测几码</label>
                <select name="digits" class="form-control">
                    <option value="">全部</option>
                    <?php foreach (Prediction_buy_model::$digitsList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['digits']) && $where['digits'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>状态</label>
                <select name="status" class="form-control">
                    <option value="">全部</option>
                    <?php foreach (Prediction_buy_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['status']) && $where['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
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
                    <th><?= sort_title('digits', '预测几码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('numbers', '预测值', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('price', '价格', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('status', '状态', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_time', '修改时间', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_by', '更新者', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['user_name'] ?></td>
                        <td><?= $lottery[$row['lottery_id']] ?></td>
                        <td><?= $row['qishu'] ?></td>
                        <td><?= $row['name'].prediction_buy_model::$digitsList[$row['digits']] ?></td>
                        <td><?= $row['numbers'] ?></td>
                        <td><?= $row['price'] ?>元</td>
                        <td><?= prediction_buy_model::$statusList[$row['status']] ?></td>
                        <td><?= $row['update_time'] ?></td>
                        <td><?= $row['update_by'] ?></td>
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