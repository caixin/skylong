<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<link href="<?= base_url('static/dist/css/odds_control_style.css') ?>" rel="stylesheet" type="text/css">
<style>
.col-md-1 {
    width: 12%;
    padding-right:3px;
    padding-left:3px;
}
.col-md-11 {
    width: 88%;
    padding-right:3px;
    padding-left:3px;
}
table, .box-header {
    vertical-align:middle;
    border:2px #A4A4A4 solid;
    border-collapse:inherit;
}
.btn_add {
    color: red;
    background-color: #fff;
    border: 1px solid gray;
    margin: 0 3px 0 3px;
}
.btn_sub {
    background-color: #fff;
    border: 1px solid gray;
    margin: 0 3px 0 3px;
}
.ball_red {
    width: 26px;
    border:2px solid red;
    border-radius:50px;
    font-weight:bold;
    font-size: 18px;
}
.ball_blue {
    width: 26px;
    border:2px solid blue;
    border-radius:50px;
    font-weight:bold;
    font-size: 18px;
}
.ball_green {
    width: 26px;
    border:2px solid green;
    border-radius:50px;
    font-weight:bold;
    font-size: 18px;
}
</style>
<div class="box">
    <!-- Custom Tabs -->
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <?php foreach ($lottery as $key => $val) : ?>
                <li class="<?= $lottery_id == $key ? 'active' : '' ?>"><a href="<?= site_url("$this->cur_url/$key") ?>"><?= $val ?></a></li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content">
            <div class="box-header">
                <form method="post" action="">
                    <div class="col-xs-1" style="width:auto;">
                        <label>运营商名称</label>
                        <select name="operator_id" class="form-control">
                            <?php foreach ($operator as $key => $val) : ?>
                                <option value="<?= $key ?>" <?= isset($where['operator_id']) && $where['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-xs-1">
                        <label>&nbsp;</label>
                        <button type="submit" class="form-control btn btn-primary">查询</button>
                    </div>
                </form>
            </div>
            <div class="box-body table-responsive no-padding">
                <div id="leftmenu" class="col-md-1">
                    <table class="table table-hover">
                        <tr style="background-color:#ECFCDD;">
                            <th>虛貨(0)</th>
                        </tr>
                    </table>
                </div>
                <div class="col-md-11">
                    <div class="box-header" style="background-color:#ECFCDD;">
                        <div id="opendata" class="box-title"></div><br />
                        <div class="box-title" style="margin:10px 0 0 0;width:100%">
                            <span>
                                号码:<input type="text" id="numbers" value="">
                                <button type="button" id="submit">送出</button>
                            </span>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <select id="sort">
                                <option value="sort">按球号排列</option>
                                <option value="total">按总额排列</option>
                                <option value="profit">按盈亏排列</option>
                            </select>
                            <label>每次升降: </label>
                            <select id="odds_adjust" style="width:70px;">
                                <option value="0.001">0.001</option>
                                <option value="0.005">0.005</option>
                                <option value="0.01">0.01</option>
                                <option value="0.05">0.05</option>
                                <option value="0.1">0.1</option>
                                <option value="0.5">0.5</option>
                                <option value="1">1</option>
                            </select>
                            <label>統計 : </label>
                            <select id="statistics" style="width:60px;">
                                <option value="0">虚货</option>
                                <option value="1">实货</option>
                            </select>
                            <label>盤型 : </label>
                            <select id="type" style="width:60px;">
                                <option value="0">全部</option>
                            </select>
                            <span style="float:right;">
                                <label>更新: <strong><b id="refresh">30</b>秒</strong></label>
                                <select id="sec" style="width:60px;">
                                    <option value="5">5秒</option>
                                    <option value="10">10秒</option>
                                    <option value="20">20秒</option>
                                    <option value="30" selected>30秒</option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div id="result_" style="margin-top:5px;"></div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.tab-content -->
    </div>
    <!-- nav-tabs-custom -->
</div>
<script type="text/javascript" src="<?=base_url("static/dist/js/jquery.countdown.min.js")?>"></script>
<script type="text/javascript">

var sort = $('#sort').val(); //哪个字段排序
var wanfa_id = <?=$wanfa_id?>;
var qishu = 0;
var adjust = $('#odds_adjust').val();
var statistics = $('#statistics').val();
var type = $('#type').val();
var sec = $('#sec').val();

//寫入開獎號碼
$('#submit').click(function(){
    $.post("<?=site_url("$this->cur_url/$lottery_id/operator_id/$where[operator_id]")?>",{
        'position': 'numbers',
        'qishu': qishu,
        'numbers': $('#numbers').val()
	},function(msg){
        var list = msg.resultinfo.list;
        alert(list.message);
        $('#numbers').val('');
    },"json");
});

function leftmenu(){
    $.post("<?=site_url("$this->cur_url/$lottery_id/operator_id/$where[operator_id]")?>",{
        'position'  : 'left',
        'wanfa_id'  : wanfa_id,
        'qishu'     : qishu,
        'statistics': statistics
	},function(list){
        var shtml = '<table class="table table-hover">';
        for(var i in list) {
            if (list[i]['id'] == '') {
                shtml += '<tr style="background-color:#ECFCDD;"><th>' + list[i]['name'] + ' <span> (' + list[i]['total'] + ')</span></th></tr>';
            } else {
                shtml += '<tr style="background-color:'+list[i]['color']+';cursor: pointer;"><td onclick="left_action('+list[i]['id']+');">' + list[i]['name'] + ' <span> (' + list[i]['total'] + ')</span></td></tr>';
            }
        }
        shtml += '</table>';
        $("#leftmenu").html(shtml);
    },"json");
}

function opendata(){
    $.post("<?=site_url("$this->cur_url/$lottery_id/operator_id/$where[operator_id]")?>",{
        'position': 'open',
        'wanfa_id': wanfa_id
	},function(list){
        var shtml = '';
        shtml += '<span>第' + list.next_qishu + '期 <strong style="color:blue;"> ' + list.wanfa_name + '</strong></span>';
        shtml += '<span style="margin-left:50px;">距封盤: <span id="getStarted"> ' + list.count_down + '</span></span>';
        shtml += '<span style="margin-left:50px;">上期輸贏: <span> ' + list.profit + '</span></span>';
        shtml += '<span style="margin-left:50px;">' + list.qishu + '期: ' + list.numbers + '</span>';
        $("#opendata").html(shtml);
        qishu = list.next_qishu;
        countdown_close(list.count_down);
    },"json");
}

function rightlist()
{
    $.post("<?=site_url("$this->cur_url/$lottery_id/operator_id/$where[operator_id]")?>",{
        'position'  : 'list',
        'wanfa_id'  : wanfa_id,
        'sort'      : sort,
        'statistics': statistics,
	},function(list){
        var shtml = '';
        if (<?=$lottery_type_id?> == 8) {
            shtml += '<table class="table table-hover">';
            for(var i in list) {
                shtml += '<tr style="background-color:#ECFCDD;">';
                for(var j in list[i]) {
                    shtml += '<th>号</th>';
                    shtml += '<th>赔率 / 特殊賠率</th>';
                    shtml += '<th>总额</th>';
                    shtml += '<th>盈亏</th>';
                    shtml += '<th>单补</th>';
                    shtml += '<th>已补</th>';
                }
                shtml += '</tr>';
                break;
            }
            for(var i in list) {
                shtml += '<tr>';
                for(var j in list[i]) {
                    if (typeof list[i][j] !== 'undefined') {
                        shtml += '<td>' + list[i][j]['values_str'] + '</td>';
                        if (statistics == 0) {
                            shtml += '<td>';
                            if (list[i][j]['show_odds'] == 1) {
                                shtml += '<button class="btn_add" onclick="edit_odds(' + list[i][j]['id'] + ',0,' + list[i][j]['interval'] + ',1)">+</button>';
                                shtml += '<span id="odds' + list[i][j]['id'] + '0">' + list[i][j]['odds'] + '</span>';
                                shtml += '<button class="btn_sub" onclick="edit_odds(' + list[i][j]['id'] + ',0,' + list[i][j]['interval'] + ',-1)">-</button>';
                            }
                            if (list[i][j]['show_odds'] == 1 && list[i][j]['show_odds_special'] == 1) {
                                shtml += '&nbsp;&nbsp;&nbsp;&nbsp; / &nbsp;&nbsp;&nbsp;&nbsp;';
                            }
                            if (list[i][j]['show_odds_special'] == 1) {
                                shtml += '<button class="btn_add" onclick="edit_odds(' + list[i][j]['id'] + ',1,' + list[i][j]['interval'] + ',1)">+</button>';
                                shtml += '<span id="odds' + list[i][j]['id'] + '1">' + list[i][j]['odds_special'] + '</span>';
                                shtml += '<button class="btn_sub" onclick="edit_odds(' + list[i][j]['id'] + ',1,' + list[i][j]['interval'] + ',-1)">-</button>';
                            }
                            shtml += '</td>';
                        } else {
                            shtml += '<td>';
                            if (list[i][j]['show_odds'] == 1) {
                                shtml += '<span id="odds' + list[i][j]['id'] + '0">' + list[i][j]['odds'] + '</span>';
                            }
                            if (list[i][j]['show_odds'] == 1 && list[i][j]['show_odds_special'] == 1) {
                                shtml += '&nbsp;&nbsp;&nbsp;&nbsp; / &nbsp;&nbsp;&nbsp;&nbsp;';
                            }
                            if (list[i][j]['show_odds_special'] == 1) {
                                shtml += '<span id="odds' + list[i][j]['id'] + '1">' + list[i][j]['odds_special'] + '</span>';
                            }
                            shtml += '</td>';
                        }
                        shtml += '<td>' + list[i][j]['total'] + '</td>';
                        shtml += '<td style="color:'+list[i][j]['profit_color']+'">' + list[i][j]['profit'] + '</td>';
                        shtml += '<td>-</td>';
                        shtml += '<td>-</td>';
                    }
                }
                shtml += '</div>';
            }
            shtml += '</table>';
        } else {
            shtml += '<div class="play_panel">';
            for(var i in list) {
                if (list[i].list.length > 15) {
                    shtml += '<div class="play_group large">';
                    shtml += '<div class="group_title">' + list[i].name + '</div>';
                    for (var v=0;v<4;v++) {
                        shtml += '<div class="sub_title">';
                        shtml += '<span class="name"><span>号</span></span>';
                        shtml += '<span class="odds"><span class="odds_value">赔率</span></span>';
                        shtml += '<span class="total">总额</span>';
                        shtml += '<span class="profit">盈亏</span>';
                        shtml += '<span class="unknow">单补</span>';
                        shtml += '<span class="done">已补</span>';
                        shtml += '</div>';
                    }
                } else {
                    shtml += '<div class="play_group">';
                    shtml += '<div class="group_title">' + list[i].name + '</div>';
                    shtml += '<div class="sub_title">';
                    shtml += '<span class="name"><span>号</span></span>';
                    shtml += '<span class="odds"><span class="odds_value">赔率</span></span>';
                    shtml += '<span class="total">总额</span>';
                    shtml += '<span class="profit">盈亏</span>';
                    shtml += '<span class="unknow">单补</span>';
                    shtml += '<span class="done">已补</span>';
                    shtml += '</div>';
                }
                for(var j in list[i].list) {
                    if (typeof list[i].list[j] != 'undefined') {
                        shtml += '<div class="item">';
                        shtml += '<span class="name"><span>' + list[i].list[j]['values_str'] + '</span></span>';
                        if (statistics == 0) {
                            shtml += '<span class="odds">';
                            if (list[i].list[j]['show_odds'] == 1) {
                                shtml += '<button class="btn_add" onclick="edit_odds(' + list[i].list[j]['id'] + ',0,' + list[i].list[j]['interval'] + ',1)">+</button>';
                                shtml += '<span class="odds_value" id="odds' + list[i].list[j]['id'] + '0">' + list[i].list[j]['odds'] + '</span>';
                                shtml += '<button class="btn_sub" onclick="edit_odds(' + list[i].list[j]['id'] + ',0,' + list[i].list[j]['interval'] + ',-1)">-</button>';
                            }
                            if (list[i].list[j]['show_odds'] == 1 && list[i].list[j]['show_odds_special'] == 1) {
                                shtml += '&nbsp;&nbsp;&nbsp;&nbsp; / &nbsp;&nbsp;&nbsp;&nbsp;';
                            }
                            if (list[i].list[j]['show_odds_special'] == 1) {
                                shtml += '<button class="btn_add" onclick="edit_odds(' + list[i].list[j]['id'] + ',1,' + list[i].list[j]['interval'] + ',1)">+</button>';
                                shtml += '<span class="odds_value" id="odds' + list[i].list[j]['id'] + '1">' + list[i].list[j]['odds_special'] + '</span>';
                                shtml += '<button class="btn_sub" onclick="edit_odds(' + list[i].list[j]['id'] + ',1,' + list[i].list[j]['interval'] + ',-1)">-</button>';
                            }
                            shtml += '</span>';
                        } else {
                            shtml += '<span class="odds">';
                            if (list[i].list[j]['show_odds'] == 1) {
                                shtml += '<span class="odds_value" id="odds' + list[i].list[j]['id'] + '0">' + list[i].list[j]['odds'] + '</span>';
                            }
                            if (list[i].list[j]['show_odds'] == 1 && list[i].list[j]['show_odds_special'] == 1) {
                                shtml += '&nbsp;&nbsp;&nbsp;&nbsp; / &nbsp;&nbsp;&nbsp;&nbsp;';
                            }
                            if (list[i].list[j]['show_odds_special'] == 1) {
                                shtml += '<span class="odds_value" id="odds' + list[i].list[j]['id'] + '1">' + list[i].list[j]['odds_special'] + '</span>';
                            }
                            shtml += '</span>';
                        }
                        shtml += '<span class="total">' + list[i].list[j]['total'] + '</span>';
                        shtml += '<span class="profit" style="color:'+list[i].list[j]['profit_color']+'">' + list[i].list[j]['profit'] + '</span>';
                        shtml += '<span class="unknow">-</span>';
                        shtml += '<span class="done">-</span>';
                        shtml += '</div>';
                    }
                }
                shtml += '</div>';
            }
            shtml += '</div>';
        }
        $("#result_").html(shtml);
    },"json");
}

function edit_odds(wanfa_detail_id,special,interval,adjust_type)
{
    $.post("<?=site_url("$this->cur_url/$lottery_id/operator_id/$where[operator_id]")?>",{
        'position': 'edit_odds',
        'qishu': qishu,
        'wanfa_detail_id': wanfa_detail_id,
        'adject_odds': adjust * adjust_type,
        'special': special,
        'interval': interval,
	},function(list){
        $('#odds'+wanfa_detail_id+special).html(list.odds);
    },"json");
}
leftmenu();
opendata();
rightlist();

function left_action(id)
{
    wanfa_id = id;
    leftmenu();
    opendata();
    rightlist();
}

$('#sort').change(function(){
    sort = $('#sort').val();
    rightlist();
});
$('#odds_adjust').change(function(){
    adjust = $('#odds_adjust').val();
});
$('#statistics').change(function(){
    statistics = $('#statistics').val();
    leftmenu();
    rightlist();
});
$('#type').change(function(){
    type = $('#type').val();
});
$('#sec').change(function(){
    sec = $('#sec').val();
});

function countdown_close(count_down)
{
    $("#getStarted").countdown(count_down, function(event) {
        $(this).text(event.strftime('%d天 %H:%M:%S'));
    });
}

setInterval(function(){
    if (sec == 0) {
        leftmenu();
        opendata();
        rightlist();
        sec = $('#sec').val();
    }
    $('#refresh').html(sec--);
},1000);
</script>