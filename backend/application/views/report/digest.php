<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>时间</label>
                <div class="input-group" style="width:200px;">
                    <input type="text" id="day_time_from" name="day_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['day_time1']) ? $where['day_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="day_time_to" name="day_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['day_time2']) ? $where['day_time2'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>&nbsp;</label>
                <button type="submit" class="form-control btn btn-primary">查询</button>
            </div>
        </form>
        <div class="col-xs-1" style="width:auto;float:right;">
            <label>&nbsp;</label>
            <a href="<?= site_url("{$this->router->class}/digest_export/$params_uri") ?>" class="form-control btn btn-primary">汇出</a>
        </div>
        <div class="col-xs-1" style="width:auto;float:right;">
            <label>最后更新时间</label>
            <input type="text" class="form-control" value="<?= $latest['update_time'] ?>" disabled>
        </div>
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
                    <th><?= sort_title('day_time', '日期', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('register_people', '注册人数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('login_people', '登录人数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('first_recharge_people', '首充人数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('first_recharge_money', '首充金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('recharge_people', '充值人数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('withdraw_people', '提现人数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('recharge_money', '充值金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('withdraw_money', '提现金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('real_income', '资金汇总', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_people', '投注人数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_number', '投注注数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('p_value', '投注金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('c_value', '中奖金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('return_point_amount', '返点金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('income', '盈亏', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                <tr>
                    <td><?= $row['day_time'] ?></td>
                    <td><?= $row['register_people'] ?></td>
                    <td><?= $row['login_people'] ?></td>
                    <td><?= $row['first_recharge_people'] ?></td>
                    <td><?= $row['first_recharge_money'] ?></td>
                    <td><?= $row['recharge_people'] ?></td>
                    <td><?= $row['withdraw_people'] ?></td>
                    <td><?= $row['recharge_money'] ?></td>
                    <td><?= $row['withdraw_money'] ?></td>
                    <td><?= $row['real_income'] ?></td>
                    <td><?= $row['bet_people'] ?></td>
                    <td><?= $row['bet_number'] ?></td>
                    <td><?= $row['p_value'] ?></td>
                    <td><?= $row['c_value'] ?></td>
                    <td><?= $row['return_point_amount'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($row['income']) ?>"><?= $row['income'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="color:blue;font-weight:bold;">
                    <td>总计</td>
                    <td style="color:<?= base_model::getProfitColor($footer['register_people']) ?>"><?= $footer['register_people'] ?></td>
                    <td></td>
                    <td style="color:<?= base_model::getProfitColor($footer['first_recharge_people']) ?>"><?= $footer['first_recharge_people'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['first_recharge_money']) ?>"><?= $footer['first_recharge_money'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['recharge_people']) ?>"><?= $footer['recharge_people'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['withdraw_people']) ?>"><?= $footer['withdraw_people'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['recharge_money']) ?>"><?= $footer['recharge_money'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['withdraw_money']) ?>"><?= $footer['withdraw_money'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['real_income']) ?>"><?= $footer['real_income'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['bet_people']) ?>"><?= $footer['bet_people'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['bet_number']) ?>"><?= $footer['bet_number'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['p_value']) ?>"><?= $footer['p_value'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['c_value']) ?>"><?= $footer['c_value'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['return_point_amount']) ?>"><?= $footer['return_point_amount'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['income']) ?>"><?= $footer['income'] ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
        <?= $this->pagination->create_links() ?>
    </div>
</div>