<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?=base_url("static/plugins/highcharts.js")?>"></script>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
    </div>
    <!-- /.box-header -->
    <div id="refresh" class="box-body table-responsive no-padding">
        <div style="color:red;">1) 今天往前算1,3,7,15,30天内有登入的不重复帐号数</div>
        <div style="color:red;">2) 今日往前算31天以上未登入的不重复帐号数</div>
        <div class="box-header">
            <h5 class="box-title" style="font-size: 14px;"><b>用户总计:</b> <?= $total ?></h5>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>颜色</th>
                    <th>类型</th>
                    <th>人数</th>
                    <th>百分比</th>
                    <th>平均余额</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($result as $row) : ?>
                <tr>
                    <td><?=daily_retention_model::$typeLight[$row['type']]?></td>
                    <td><?=daily_retention_model::$typeList[$row['type']]?></td>
                    <td><?=$row['day_count']?></td>
                    <td><?=$row['percent']?>%</td>
                    <td><?=$row['avg_money']?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="box-header">
            <h5 class="box-title" style="font-size: 14px;"><b>用户总计:</b> <?= $total ?></h5>
        </div>
    </div>
    <!-- /.box-body -->
</div>