<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($where['lottery_id']) ? $where['lottery_id'] : 0 ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>玩法类别</label>
                <select id="category" name="category" class="form-control">
                    <option value="">全部</option>
                    <?php foreach (daily_user_report_model::$categoryList as $key => $val) : ?>
                    <option value="<?= $key ?>" <?= isset($where['category']) && $where['category'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>代理帐号</label>
                <input type="text" name="agent_name" class="form-control" placeholder="请输入..." value="<?= isset($where['agent_name']) ? $where['agent_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>邀请码</label>
                <input type="text" name="agent_code" class="form-control" placeholder="请输入..." value="<?= isset($where['agent_code']) ? $where['agent_code'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>时间</label>
                <div class="input-group" style="width:200px;">
                    <input type="text" id="day_time_from" name="create_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['create_time1']) ? $where['create_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="day_time_to" name="create_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['create_time2']) ? $where['create_time2'] : '' ?>" autocomplete="off">
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
    <div class=" box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= sort_title('user_name', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('mobile', '手机号码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('agent_name', '代理帐号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('agent_code', '邀请码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_count', '笔数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('total_p_value', '总投注额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('c_value', '赔付额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_eff', '有效投注额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('profit', '平台输赢', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('category', '类型', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                <tr>
                    <td><a href="javascript:;" onclick="user_detail(<?= $row['user_name'] ?>);" style="<?= $row['user_type'] == 1 ? 'color:#aaaaaa;' : '' ?>"><?= $row['user_name'] ?></a></td>
                    <td><?= $row['mobile'] ?></td>
                    <td><?= $row['agent_name'] ?></td>
                    <td><?= $row['agent_code'] ?></td>
                    <td><?= $row['bet_count'] ?></td>
                    <td><?= $row['total_p_value'] ?></td>
                    <td><?= $row['c_value'] ?></td>
                    <td><?= $row['bet_eff'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($row['profit']) ?>"><?= $row['profit'] ?></td>
                    <td><?= daily_user_report_model::$categoryList[$row['category']] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="color:blue;font-weight:bold;">
                    <td>总计</td>
                    <td><?= $footer['member_count'] ?></td>
                    <td></td>
                    <td></td>
                    <td><?= $footer['bet_count'] ?></td>
                    <td><?= $footer['total_p_value'] ?></td>
                    <td><?= $footer['c_value'] ?></td>
                    <td><?= $footer['bet_eff'] ?></td>
                    <td style="color:<?= base_model::getProfitColor($footer['profit']) ?>"><?= $footer['profit'] ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="box-footer clearfix">
        <?= $this->pagination->create_links() ?>
    </div>
</div>