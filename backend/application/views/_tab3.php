<div class="box-body table-responsive no-padding">
    <div class="box-header">
		<div class="col-xs-1" style="width:auto;">
			<div class="input-group">
				<input type="text" id="tab3_date_start" class="form-control datepicker" style="width:100px;" placeholder="起始时间" value="" autocomplete="off">
				<input type="text" id="tab3_date_end" class="form-control datepicker" style="width:100px;" placeholder="结束时间" value="" autocomplete="off">
			</div>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="amonth('tab3');getChartData3();">近一月</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="month('tab3');getChartData3();">本月</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="lmonth('tab3');getChartData3();">上月</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="hyear('tab3');getChartData3();">半年</button>
		</div>
		<div class="col-xs-1">
			<button type="button" class="form-control btn btn-default" onclick="year('tab3');getChartData3();">一年</button>
		</div>
		<div class="col-xs-1">
			<button type="submit" class="form-control btn btn-primary" onclick="getChartData3();">查询</button>
		</div>
		<div class="col-xs-6" style="width:auto;">
			<ul>
				<li style="height:35px;">
						<button type="button" class="form-control btn btn-default source" style="width:75px;background: #393e51;color: #FFFFFF;" data-name="all" value="1" onclick="toggle(this);">全部</button>
					<?php foreach (base_model::$sourceList as $key => $value) : ?>
						<button type="button" class="form-control btn btn-default source" style="width:75px;" data-name="<?= $key ?>" value="0" onclick="toggle(this);"><?= $value ?></button>
					<?php endforeach; ?>
				</li>
			</ul>
		</div>
	</div>
	<div class="col-lg-12 col-xs-12">
		<div id="registerLineChart" style="width: 100%; height: 350px; margin: 0 auto"></div>
	</div>
	<div class="col-lg-12 col-xs-12">
		<div id="rechargeLineChart" style="width: 100%; height: 350px; margin: 0 auto"></div>
	</div>
</div>
<script type="text/javascript">
//切換
function toggle(obj) {
	var status = $(obj).val();
	status = Math.abs(status - 1);
	$(obj).val(status);
	if (status == 1) {
		$(obj).css('background', '#393e51');
		$(obj).css('color', '#FFFFFF');
	} else {
		$(obj).css('background', '#DDDDDD');
		$(obj).css('color', '#000000');
	}
	getChartData3();
};

function getChartData3() {
	var source = [];
	$(".source").each(function() {
		if ($(this).val() == 1) {
			source.push($(this).data('name'));
		}
	});

	$.ajax({
		type: "POST",
		url: "<?= site_url('home/index/action/3') ?>/date_start/"+
				$('#tab3_date_start').val()+
				'/date_end/'+
				$('#tab3_date_end').val()+
				'/source/'+
				source.join(),
		dataType: "json",
		async: true,
		success: function(result) {
			$('#registerLineChart').highcharts(result.registerLine);
			$('#rechargeLineChart').highcharts(result.rechargeLine);
		}
	});
};
$(document).ready(function() {
	amonth('tab3');
	getChartData3();
});
</script>