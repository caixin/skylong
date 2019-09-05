<script type="text/javascript" src="<?= base_url('static/plugins/') ?>highcharts.js"></script>
<div class="box">
	<!-- Custom Tabs -->
	<div class="nav-tabs-custom">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#tab_1" data-toggle="tab">仪表板</a></li>
			<li><a href="#tab_2" data-toggle="tab">盈亏趋势</a></li>
			<li><a href="#tab_3" data-toggle="tab">会员及盈亏趋势</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="tab_1">
				<div class="box box-success">
					<?php $this->load->view('_tab1'); ?>
				</div>
			</div>
			<!-- /.tab-pane -->
			<div class="tab-pane" id="tab_2">
				<div class="box box-success">
					<?php $this->load->view('_tab2'); ?>
				</div>
			</div>
			<!-- /.tab-pane -->
			<div class="tab-pane" id="tab_3">
				<div class="box box-success">
					<?php $this->load->view('_tab3'); ?>
				</div>
			</div>
			<!-- /.tab-pane -->
		</div>
		<!-- /.tab-content -->
	</div>
	<!-- nav-tabs-custom -->
</div>
<script type="text/javascript">

	function number_format(number, decimals, dec_point, thousands_sep, roundtag) {
		/*
		 * 参数说明：
		 * number：要格式化的数字
		 * decimals：保留几位小数
		 * dec_point：小数点符号
		 * thousands_sep：千分位符号
		 * roundtag:舍入参数，默认 "ceil" 向上取,"floor"向下取,"round" 四舍五入
		 * */
		number = (number + '').replace(/[^0-9+-Ee.]/g, '');
		roundtag = roundtag || "ceil"; //"ceil","floor","round"
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function(n, prec) {
				var k = Math.pow(10, prec);
				return '' + parseFloat(Math[roundtag](parseFloat((n * k).toFixed(prec * 2))).toFixed(prec * 2)) / k;
			};
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		var re = /(-?\d+)(\d{3})/;
		while (re.test(s[0])) {
			s[0] = s[0].replace(re, "$1" + sep + "$2");
		}

		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

	var loadding;

	//今日
	function today(action) {
		var date = new Date();
		var year = date.getFullYear();
		var month = date.getMonth() + 1;
		var day = date.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_start").val(year + "-" + month + "-" + day);
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};

	//昨天
	function yesterday(action) {
		var date = new Date();
		date.setDate(date.getDate() - 1);
		var year = date.getFullYear();
		var month = date.getMonth() + 1;
		var day = date.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_start").val(year + "-" + month + "-" + day);
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};

	//本週
	function week(action) {
		var date = new Date();
		var DayOfWeek = date.getDay(); //本週第幾天
		DayOfWeek = (DayOfWeek == 0) ? 7 : DayOfWeek;

		//本週開始日期(星期一)
		date.setDate(date.getDate() - DayOfWeek + 1);
		var year = date.getFullYear();
		var month = date.getMonth() + 1;
		var day = date.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_start").val(year + "-" + month + "-" + day);

		//本週結束日期(星期日)
		date.setDate(date.getDate() + 6);
		year = date.getFullYear();
		month = date.getMonth() + 1;
		day = date.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};

	//上週
	function lweek(action) {
		var date = new Date();
		date.setDate(date.getDate() - 7);
		var DayOfWeek = date.getDay(); //本週第幾天
		DayOfWeek = (DayOfWeek == 0) ? 7 : DayOfWeek;

		//本週開始日期(星期一)
		date.setDate(date.getDate() - DayOfWeek + 1);
		var year = date.getFullYear();
		var month = date.getMonth() + 1;
		var day = date.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_start").val(year + "-" + month + "-" + day);

		//本週結束日期(星期日)
		date.setDate(date.getDate() + 6);
		year = date.getFullYear();
		month = date.getMonth() + 1;
		day = date.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};

	//近一月
	function amonth(action) {
		var date = new Date();
		date.setDate(date.getDate() - 30);
		var year = date.getFullYear();
		var month = date.getMonth() + 1;
		var day = date.getDate();

		//30天前
		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_start").val(year + "-" + month + "-" + day);

		//今天
		date = new Date();
		year = date.getFullYear();
		month = date.getMonth() + 1;
		day = date.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};

	//本月
	function month(action) {
		var date = new Date();
		var year = date.getFullYear();
		var month = date.getMonth() + 1; //0~11
		month = month < 10 ? '0' + month : month;

		//本月開始日期
		$("#" + action + "_date_start").val(year + "-" + month + "-01");

		//本月結束日期
		var NextMonthFirstDay = new Date(date.getFullYear(), date.getMonth() + 1, 1);
		var day = new Date(NextMonthFirstDay - (1000 * 60 * 60 * 24)).getDate();
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};

	//上月
	function lmonth(action) {
		var date = new Date();
		//上個月
		var ldate = new Date(date.getFullYear(), date.getMonth() - 1, 1);
		var year = ldate.getFullYear();
		var month = ldate.getMonth() + 1; //0~11
		var day = ldate.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;

		//上月開始日期
		$("#" + action + "_date_start").val(year + "-" + month + "-" + day);

		//上月結束日期
		var MonthFirstDay = new Date(date.getFullYear(), date.getMonth(), 1);
		var LastMonthLastDay = new Date(MonthFirstDay - (1000 * 60 * 60 * 24));
		year = LastMonthLastDay.getFullYear();
		month = LastMonthLastDay.getMonth() + 1; //0~11
		day = LastMonthLastDay.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};

	//半年
	function hyear(action) {
		var date = new Date();

		//半年開始日期
		var hydate = new Date(date.getFullYear(), date.getMonth() - 5, 1);
		var year = hydate.getFullYear();
		var month = hydate.getMonth() + 1; //0~11
		var day = hydate.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_start").val(year + "-" + month + "-" + day);

		//本月結束日期
		var NextMonthFirstDay = new Date(date.getFullYear(), date.getMonth() + 1, 1);
		year = date.getFullYear();
		month = date.getMonth() + 1; //0~11
		day = new Date(NextMonthFirstDay - (1000 * 60 * 60 * 24)).getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};

	//一年
	function year(action) {
		var date = new Date();

		//一年開始日期
		var ydate = new Date(date.getFullYear(), date.getMonth() - 11, 1);
		var year = ydate.getFullYear();
		var month = ydate.getMonth() + 1; //0~11
		var day = ydate.getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_start").val(year + "-" + month + "-" + day);

		//本月結束日期
		var NextMonthFirstDay = new Date(date.getFullYear(), date.getMonth() + 1, 1);
		year = date.getFullYear();
		month = date.getMonth() + 1; //0~11
		day = new Date(NextMonthFirstDay - (1000 * 60 * 60 * 24)).getDate();

		month = month < 10 ? '0' + month : month;
		day = day < 10 ? '0' + day : day;
		$("#" + action + "_date_end").val(year + "-" + month + "-" + day);
	};
</script>