<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>IP</label>
                <input type="text" name="ip" class="form-control" placeholder="请输入..." value="<?= isset($where['ip']) ? $where['ip'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>执行时间(>=秒)</label>
                <input type="text" name="exec_time" class="form-control" placeholder="请输入..." value="<?= isset($where['exec_time']) ? $where['exec_time'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>执行时间</label>
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
                    <th><?= sort_title('ip', 'IP', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('controllers', '控制向', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('functions', '函式', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('param', '参数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('return_str', '回传值', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('exec_time', '执行秒数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '执行时间', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['ip'] ?></td>
                        <td><?= $row['controllers'] ?></td>
                        <td><?= $row['functions'] ?></td>
                        <td style="word-break: break-word;width: 20%;"><?= $row['param'] ?></td>
                        <td style="word-break: break-word;width: 30%;"><?= $row['return_str'] ?></td>
                        <td><?= $row['exec_time'] ?></td>
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