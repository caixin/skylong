<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>帐户类型</label>
                <select name="money_type" class="form-control">
                    <?php foreach (user_model::$moneyTypeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['money_type']) && $where['money_type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>棋牌玩法</label>
                <select name="special_id" class="form-control">
                    <option value="">全部</option>
                    <?php foreach ($special as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['special_id']) && $where['special_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>投注期数</label>
                <input type="text" name="qishu" class="form-control" placeholder="请输入..." value="<?= isset($where['qishu']) ? $where['qishu'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>状态</label>
                <select name="status" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (Ettm_special_bet_record_model::$statusList as $key => $val) : ?>
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
                    <th><?= sort_title('special_id', '棋牌玩法', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('qishu', '期数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_time', '开奖时间', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('numbers', '开奖号码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('card_type', '形态', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_count', '投注人数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('total_p_value', '投注总额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('win_bet_count', '中奖人数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('c_value', '中奖总额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('profit', '平台盈亏', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('status', '开奖状态', $this->cur_url, $order, $where) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $special[$row['special_id']] ?></td>
                        <td><?= $row['qishu'] ?></td>
                        <td><?= $row['lottery_time'] ?></td>
                        <td><?= $row['numbers'] ?></td>
                        <td><?= $row['card_type'] ?></td>
                        <td><?= $row['bet_count'] ?></td>
                        <td><?= $row['total_p_value'] ?></td>
                        <td><?= $row['win_bet_count'] ?></td>
                        <td><?= $row['c_value'] ?></td>
                        <td style="color:<?= $row['profit_color'] ?>;"><?= $row['profit'] ?></td>
                        <td><?= Ettm_lottery_record_model::$statusList[$row['status']] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/refund", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="layer_open('<?= $row['detail_url'] ?>')">详情</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="color:blue;font-weight:bold;">
                    <td>当页总计</td>
                    <td colspan="5"></td>
                    <td><?= $footer['total_p_value'] ?></td>
                    <td></td>
                    <td><?= $footer['c_value'] ?></td>
                    <td style="color:<?= $footer['profit_color'] ?>;"><?= $footer['profit'] ?></td>
                </tr>
                <tr style="color:blue;font-weight:bold;">
                    <td>总计</td>
                    <td colspan="5"></td>
                    <td><?= $footer_total['total_p_value'] ?></td>
                    <td></td>
                    <td><?= $footer_total['c_value'] ?></td>
                    <td style="color:<?= $footer_total['profit_color'] ?>;"><?= $footer_total['profit'] ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
        <?= $this->pagination->create_links() ?>
    </div>
</div>