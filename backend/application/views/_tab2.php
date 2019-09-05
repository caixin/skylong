<div class="box-body table-responsive no-padding">
    <div class="box-header">
		<div class="col-xs-1" style="width:auto;">
			<div class="input-group">
				<input type="text" id="tab2_date_start" class="form-control datepicker" style="width:100px;" placeholder="起始时间" value="" autocomplete="off">
				<input type="text" id="tab2_date_end" class="form-control datepicker" style="width:100px;" placeholder="结束时间" value="" autocomplete="off">
			</div>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="week('tab2');getChartData2();">本周</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="lweek('tab2');getChartData2();">上周</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="month('tab2');getChartData2();">本月</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="lmonth('tab2');getChartData2();">上月</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="hyear('tab2');getChartData2();">半年</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="year('tab2');getChartData2();">一年</button>
		</div>
		<div class="col-xs-1">
			<button type="submit" class="form-control btn btn-primary" onclick="getChartData2();">查询</button>
		</div>
	</div>
	<div class="col-lg-12 col-xs-12">
		<div id="lineChart" style="width: 100%; height: 350px; margin: 0 auto"></div>
	</div>
	<div class="col-lg-6 col-xs-12">
		<div id="pieChart" style="width: 100%; height: 350px; margin: 0 auto"></div>
	</div>
	<div class="col-lg-6 col-xs-12">
		<div class="box">
			<div class="box-body no-padding">
				<table class="table table-striped" id="tableList"></table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
function getChartData2() {
	$.ajax({
		type: "POST",
		url: "<?= site_url('home/index/action/2') ?>/date_start/"+
				$('#tab2_date_start').val()+
				'/date_end/'+
				$('#tab2_date_end').val(),
		dataType: "json",
		async: true,
		success: function(result) {
			$('#lineChart').highcharts(result.lineChart);
			$('#pieChart').highcharts(result.pieChart);
			var shtml = '';
			shtml += '<tbody>';
			shtml += '<tr>';
			shtml += '<th>运营商名称</th>';
			shtml += '<th>出入款盈亏金额</th>';
			shtml += '<th>出入款盈亏占比</th>';
			shtml += '</tr>';
			for (var i in result.tableList) {
				var color = "red";
				if (result.tableList[i].profit <= 0) color = "green";
				shtml += '<tr>';
				shtml += '<td style="font-size: 16px;font-weight: bold;">' + result.tableList[i]['operator_name'] + '</td>';
				shtml += '<td style="color:' + color + '">' + number_format(result.tableList[i]['profit'], 2, '.', ',') + '</td>';
				shtml += '<td>' + result.tableList[i]['percentage'] + '%</td>';
				shtml += '</tr>';
			}
			shtml += '</tbody>';

			$("#tableList").html(shtml);
		}
	});
};
$(document).ready(function() {
	week('tab2');
	getChartData2();
});
</script>
