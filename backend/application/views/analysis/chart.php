<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?=base_url("static/plugins/highcharts.js")?>"></script>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>统计类型</label>
                <select name="type" class="form-control">
                    <?php foreach (Daily_analysis_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
	    <div id="chart"></div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>时间</th>
                    <th>人数</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($table as $key => $val) : ?>
                <tr>
                    <td><?=date('Y-m-d', $key)?></td>
                    <td><?=$val?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <script type="text/javascript">
        $(function () {
            $('#chart').highcharts({
                chart: {
                    type: 'column'
                },
                title: {
                    text: '<?=Daily_analysis_model::$typeList[$where['type']]?>'
                },
                credits: {
                    enabled : false
                },
                xAxis: {
                    type: 'category',
                    title: {
                        text: '日期'
                    },
                    labels: {
                        rotation: -45,
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif',
                            fontWeight: 'bold'
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: '人数'
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '人数: <b>{point.y}</b>'
                },
                plotOptions: {
                    column: {
                        depth: 25
                    }
                },
                series: [{
                    name: '人数',
                    colorByPoint: true,
                    data: <?=json_encode($chart)?>,
                    dataLabels: {
                        enabled: 'true',
                        color: '#000000',
                        style: {
                            fontSize: '14px',
                            fontFamily: 'Verdana, sans-serif',
                            textShadow: '0 0 2px black'
                        }
                    }
                }]
            });
        });
        </script>
    </div>
    <!-- /.box-body -->
</div>