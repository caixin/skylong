<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>操作人</label>
                <input type="text" name="create_by" class="form-control" placeholder="请输入..." value="<?= isset($where['create_by']) ? $where['create_by'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>登入IP</label>
                <input type="text" name="ip" class="form-control" placeholder="请输入..." value="<?= isset($where['ip']) ? $where['ip'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>添加日期</label>
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
                    <th><?= sort_title('ip', '登入IP', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('status', '状态', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '添加日期', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_by', '操作人', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['ip'] ?></td>
                        <td><?= admin_login_log_model::$statusList[$row['status']] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td><?= $row['create_by'] ?></td>
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