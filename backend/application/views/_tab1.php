<div class="box-body table-responsive no-padding">
	<div class="box-header">
		<div class="col-xs-12" style="margin:5px 0 10px 0;" id="topResult"></div>
		<div class="col-xs-1" style="width:auto;">
			<div class="input-group">
				<input type="text" id="tab1_date_start" class="form-control datepicker" style="width:100px;" placeholder="起始时间" value="" autocomplete="off">
				<input type="text" id="tab1_date_end" class="form-control datepicker" style="width:100px;" placeholder="结束时间" value="" autocomplete="off">
			</div>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="today('tab1');getChartData1();">今日</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="yesterday('tab1');getChartData1();">昨日</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="week('tab1');getChartData1();">本周</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="lweek('tab1');getChartData1();">上周</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="month('tab1');getChartData1();">本月</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="lmonth('tab1');getChartData1();">上月</button>
		</div>
		<div class="col-xs-1">
			<button type="submit" class="form-control btn btn-primary" onclick="getChartData1();">查询</button>
		</div>
	</div>
	<div class="col-xs-12" id="downResult"></div>
</div>
<script type="text/javascript">
	function getChartData1() {
		$.ajax({
			type: "POST",
			url: "<?= site_url('home/index/action/1') ?>/date_start/" + $('#tab1_date_start').val() + '/date_end/' + $('#tab1_date_end').val(),
			dataType: "json",
			async: true,
			success: function(result) {
				var shtml = '';
				$.each(result.topResult, function(key, value) {
					shtml += '<b style="font-size: 18px; margin-left:10px;">' + value['title'] + '</b>';
					if (value['link']) {
						shtml += '<a href="' + value['link'] + '"><b style="font-size: 24px;color: red;">' + value['value'] + '</b></a>';
					} else {
						shtml += '<b style="font-size: 24px;color: red;">' + value['value'] + '</b>';
					}
				})
				$("#topResult").html(shtml);

				shtml = '';
				$.each(result.downResult, function(key, value) {
					shtml += '<div class="row" style="margin-right: 0px;margin-left: 10px">';
					shtml += '<div class="col-xs-12">';
					shtml += '<span class="pull-left" style="font-size: 25px;font-weight: bold; margin-top: 20px">' + value['title'] + '</span>';
					shtml += '</div>';
					shtml += '</div>';
					shtml += '<div class="progress" style="height: 2px">';
					shtml += '<div class="progress-bar" style="width: 100%"></div>';
					shtml += '</div>';
					$.each(value.value, function(key2, value2) {
						var color = '#000';
						if (value2['color'] == 1) {
							if (value2['data'] > 0) {
								color = 'red';
							} else if (value2['data'] < 0) {
								color = 'green';
							}
						}
						shtml += '<div class="col-lg-3 col-md-4 col-xs-6">';
						shtml += '<div class="small-box bg-gray">';
						shtml += '<div class="row" style="margin-right: 0px;margin-left: 0px;">';
						shtml += '<div class="col-xs-12">';
						shtml += '<span class="pull-left" style="font-size: 25px;font-weight: bold;">' + value2['title'] + '</span>';
						shtml += '</div>';
						shtml += '</div>';
						shtml += '<div class="progress" style="height: 2px;">';
						shtml += '<div class="progress-bar" style="width: 70%"></div>';
						shtml += '</div>';
						shtml += '<div class="row" style="margin-right: 0px;margin-left: 0px;">';
						shtml += '<div class="col-xs-12">';
						shtml += '<h3 style=color:' + color + '>' + number_format(value2['data'], 2, '.', ',') + '</h3>';
						shtml += '</div>';
						shtml += '</div>';
						shtml += '</div>';
						shtml += '</div>';
					})
				})
				$("#downResult").html(shtml);
			}
		});
	};
	$(document).ready(function() {
		today('tab1');
		getChartData1();
	});
</script>