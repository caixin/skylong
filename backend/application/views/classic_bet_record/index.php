<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
            <input type="hidden" name="order_sn" value="<?= isset($where['order_sn']) ? $where['order_sn'] : '' ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>彩种</label>
                <select name="lottery_id" class="form-control">
                    <option value="">全部</option>
                    <?php foreach ($lottery as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['lottery_id']) && $where['lottery_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>投注用户</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>投注期数</label>
                <input type="text" name="qishu" class="form-control" placeholder="请输入..." value="<?= isset($where['qishu']) ? $where['qishu'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>状态</label>
                <select name="status" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (Ettm_classic_bet_record_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['status']) && $where['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>下注时间</label>
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
                    <th><?= sort_title('uid', '投注用户', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_id', '彩种名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('qishu', '期数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('values', '玩法', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_values_str', '投注值', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('odds', '赔率', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_number', '注数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('total_p_value', '投注总额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('c_value', '派奖金额', $this->cur_url, $order, $where) ?></th>
                    <th>有效下注额</th>
                    <th>平台输赢</th>
                    <th><?= sort_title('status', '状态', $this->cur_url, $order, $where) ?></th>
                    <th width="90"><?= sort_title('create_time', '下注时间', $this->cur_url, $order, $where) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);" style="<?=$row['user_type'] == 1 ? 'color:#aaaaaa;':''?>"><?= $row['user_name'] ?></a></td>
                        <td><?= $lottery[$row['lottery_id']] ?></td>
                        <td><?= $row['qishu'] ?></td>
                        <td><?= $row['values'] ?></td>
                        <td><?= $row['bet_values_str'] ?></td>
                        <td><?= $row['odds'] ?></td>
                        <td><?= $row['bet_number'] ?></td>
                        <td><?= $row['total_p_value'] ?></td>
                        <td><?= $row['c_value'] ?></td>
                        <td><?= $row['bet_eff'] ?></td>
                        <td style="color:<?=base_model::getProfitColor($row['profit'])?>"><?= $row['profit'] ?></td>
                        <td><?= Ettm_classic_bet_record_model::$statusList[$row['status']] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/refund", $this->allow_url)) : ?>
                                <?php if ($row['status'] < 2): ?>
                                <button type="button" class="btn btn-primary" onclick="refund(<?= $row['id'] ?>)">退款</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="color:blue;font-weight:bold;">
                    <td colspan="2"></td>
                    <td>当页总计</td>
                    <td colspan="5"></td>
                    <td><?= $footer['total_p_value'] ?></td>
                    <td><?= $footer['c_value'] ?></td>
                    <td><?= $footer['bet_eff'] ?></td>
                    <td style="color:<?=base_model::getProfitColor($footer['profit'])?>"><?= $footer['profit'] ?></td>
                </tr>
                <tr style="color:blue;font-weight:bold;">
                    <td colspan="2"></td>
                    <td>总计</td>
                    <td colspan="5"></td>
                    <td><?= $footer_total['total_p_value'] ?></td>
                    <td><?= $footer_total['c_value'] ?></td>
                    <td><?= $footer_total['bet_eff'] ?></td>
                    <td style="color:<?=base_model::getProfitColor($footer_total['profit'])?>"><?= $footer_total['profit'] ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
        <?= $this->pagination->create_links() ?>
    </div>
</div>
<script>
    function refund(id) {
        if (confirm('您确定要退款吗?')) {
            $.post('<?= site_url("{$this->router->class}/refund") ?>', {
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