<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= sort_title('from_uid', '下级用户', $cur_url, $order, $where) ?></th>
                    <th><?= sort_title('category', '玩法类别', $cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_id', '彩种', $cur_url, $order, $where) ?></th>
                    <th><?= sort_title('qishu', '期数', $cur_url, $order, $where) ?></th>
                    <th><?= sort_title('amount', '返点值', $cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '返点时间', $cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['user_name'] ?></td>
                        <td><?= agent_return_point_model::$categoryList[$row['category']] ?></td>
                        <td><?= $lottery[$row['lottery_id']] ?></td>
                        <td><?= $row['qishu'] ?></td>
                        <td><?= $row['amount'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>