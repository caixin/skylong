<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>时间</label>
                <div class="input-group">
                    <input type="text" id="create_time_from" name="day_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['day_time1']) ? $where['day_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="create_time_to" name="day_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['day_time2']) ? $where['day_time2'] : '' ?>" autocomplete="off">
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
	    <div style="color:red;">选择期间每一天的登入状况(保留率延伸表格)</div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>颜色</th>
                    <th>类型</th>
                <?php for ($i=strtotime($where['day_time1']);$i<=strtotime($where['day_time2']);$i+=86400): ?>
                    <th><?=date('Y-m-d', $i)?></th>
                <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($table as $type => $row): ?>
                <tr>
                <td><?=daily_retention_model::$typeLight[$type]?></td>
                <td><?=daily_retention_model::$typeList[$type]?></td>
                <?php for ($i=strtotime($where['day_time1']);$i<=strtotime($where['day_time2']);$i+=86400): ?>
                    <th><?=$row[$i]?></th>
                <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
	    <div id="chart" style="width:99%;"></div>
    </div>
    <!-- /.box-body -->
</div>
<script type="text/javascript" src="<?=base_url("static/plugins/highcharts.js")?>"></script>
<script>
$(function () {
    $('#chart').highcharts({
		chart: {
            type: 'line',
			height: 600 
        },
        title: {
            text: '<?=$this->title?>'
        },
		credits: {
			enabled : false
		},
        xAxis: {
			categories: <?=json_encode($date)?>,
			labels: {
                rotation: -45,
                style: {
                    fontSize: '13px',
                    fontFamily: 'Verdana, sans-serif'
                }
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: '人数'
            },
			plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
		legend: {
			shadow: true
		},
        tooltip: {
            pointFormat: '人数: <b>{point.y}</b>'
        },
		plotOptions: {
            column: {
                depth: 25
            }
        },
        series: <?=json_encode($chart)?>
    });
});
</script>