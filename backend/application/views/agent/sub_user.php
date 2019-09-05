<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= sort_title('id', '编号', $cur_url, $order, $where) ?></th>
                    <th><?= sort_title('user_name', '用户名', $cur_url, $order, $where) ?></th>
                    <th>线下会员</th>
                    <th>充值</th>
                    <th>提现</th>
                    <th>彩金</th>
                    <th>返水</th>
                    <th>代理返点</th>
                    <th><?= sort_title('create_time', '注册时间', $cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['user_name'] ?></td>
                        <td><a href="javascript:sub_user(<?= $row['id'] ?>)"><?= $row['user_number'] ?></a></td>
                        <td><?= $row['recharge_money'] ?></td>
                        <td><?= $row['withdraw_money'] ?></td>
                        <td><?= $row['bonus_money'] ?></td>
                        <td><?= $row['rakeback_money'] ?></td>
                        <td><?= $row['return_point'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    //下級會員
    function sub_user(uid) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['95%', '90%'],
            content: '<?= site_url("{$this->router->class}/sub_user/$starttime/$endtime/agent_pid") ?>/' + uid
        });
    }
</script>