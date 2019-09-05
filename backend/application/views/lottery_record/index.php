<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>彩种名称</label>
                <select name="lottery_id" class="form-control">
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
                <label>开奖时间</label>
                <div class="input-group">
                    <input type="text" id="lottery_time_from" name="lottery_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['lottery_time1']) ? $where['lottery_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="lottery_time_to" name="lottery_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['lottery_time2']) ? $where['lottery_time2'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>狀態</label>
                <select name="status" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (ettm_lottery_record_model::$statusList as $key => $val) : ?>
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
                    <th><?= sort_title('lottery_name', '彩种名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('qishu', '期数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('numbers', '开奖号码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_time', '开奖时间', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_time', '更新时间', $this->cur_url, $order, $where) ?></th>
                    <th>投注人数</th>
                    <th>投注注单</th>
                    <th>投注总额</th>
                    <th>中奖人数</th>
                    <th>中奖总额</th>
                    <th>平台盈亏</th>
                    <th><?= sort_title('status', '狀態', $this->cur_url, $order, $where) ?></th>
                    <th width="240"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['lottery_name'] ?></td>
                        <td><?= $row['qishu'] ?></td>
                        <td><?= $row['numbers'] ?></td>
                        <td><?= $row['lottery_time'] ?></td>
                        <td><?= $row['update_time'] ?></td>
                        <td><?= $row['bet_count'] ?></td>
                        <td><?= $row['bet_number'] ?></td>
                        <td><?= $row['total_p_value'] ?></td>
                        <td><?= $row['win_bet_count'] ?></td>
                        <td><?= $row['c_value'] ?></td>
                        <td style="color:<?=base_model::getProfitColor($row['profit'])?>"><?= $row['profit'] ?></td>
                        <td><?= ettm_lottery_record_model::$statusList[$row['status']] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="edit(<?= $row['id'] ?>)">编辑</button>
                            <?php endif; ?>
                            <?php if (($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/refund", $this->allow_url)) && $row['status'] != 2) : ?>
                                <button type="button" class="btn btn-primary" onclick="refund(<?= $row['id'] ?>)">退款</button>
                            <?php endif; ?>
                            <?php if (($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/reaward", $this->allow_url)) && $row['status'] != 0) : ?>
                                <button type="button" class="btn btn-primary" onclick="reaward(<?= $row['id'] ?>)">重新派奖</button>
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
    function edit(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/edit") ?>/' + id
        });
    }
    //退款
    function refund(id) {
        if (confirm('您确定要退款吗?')) {
            $.post('<?= site_url("{$this->router->class}/refund") ?>', {
                'id': id
            }, function(data) {
                if (data == 'done') {
                    alert('已退款完成!');
                    location.reload();
                } else {
                    alert('操作失败!');
                }
            });
        }
    }
    //重新派獎
    function reaward(id) {
        if (confirm('您确定要重新派奖吗?')) {
            $.post('<?= site_url("{$this->router->class}/reaward") ?>', {
                'id': id
            }, function(data) {
                if (data == 'done') {
                    location.reload();
                } else {
                    alert('操作失败!');
                }
            });
        }
    }
</script>