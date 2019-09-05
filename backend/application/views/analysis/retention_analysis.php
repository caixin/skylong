<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?=base_url("static/plugins/highcharts.js")?>"></script>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>时间</label>
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
    <!-- /.box-header -->
    <div id="refresh" class="box-body table-responsive no-padding">
	    <div style="color:red;">选择日新增帐号1,3,7,30,60,90天保留率及流失率</div>
        <div class="box-header">
            <h5 class="box-title" style="font-size: 14px;"><b>用户总计:</b> <?= $total ?></h5>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>类型</th>
                    <th>人数</th>
                    <th>百分比</th>
                    <th>平均余额</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?=daily_retention_model::$analysis_typeList[$row['type']]?></td>
                    <td><?=$row['count']?></td>
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