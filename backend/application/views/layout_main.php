<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $this->site_config['web_title_back'] ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="<?= base_url('static/bower_components/bootstrap/dist/css/bootstrap.min.css') ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('static/bower_components/font-awesome/css/font-awesome.min.css') ?>">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= base_url('static/bower_components/Ionicons/css/ionicons.min.css') ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('static/dist/css/AdminLTE.min.css') ?>">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="<?= base_url('static/dist/css/skins/_all-skins.min.css') ?>">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <!-- jQuery 3 -->
    <script src="<?= base_url('static/bower_components/jquery/dist/jquery.min.js') ?>"></script>
    <!-- Layer -->
    <link href="<?= base_url('static/plugins/layer/skin/layer.css') ?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?= site_url('static/bower_components/select2/dist/css/select2.min.css') ?>">
    <script src="<?= base_url('static/plugins/layer/layer.js') ?>"></script>
    <!-- timepicker -->
    <link href="<?= base_url('static/plugins/jQueryUI/ui-lightness/jquery-ui-1.10.4.custom.css') ?>" rel="stylesheet" type="text/css">
    <script src="<?= base_url('static/plugins/jQueryUI/jquery-ui-1.10.4.custom.js') ?>"></script>
    <link href="<?= base_url('static/plugins/jQueryUI/jquery-ui-timepicker-addon.css') ?>" rel="stylesheet" type="text/css">
    <script src="<?= base_url('static/plugins/jQueryUI/jquery-ui-timepicker-addon.js') ?>"></script>
    <script src="<?= base_url('static/plugins/jQueryUI/ui.datepicker-zh-CN.js') ?>"></script>
    <script src="<?= site_url('static/bower_components/select2/dist/js/select2.full.min.js') ?>"></script>
    <style>
        .input-group .form-control {
            z-index: 1;
        }

        .ui-datepicker-title {
            color: black;
        }

        .table a.sort {
            background-position: 100% 45%;
            background-repeat: no-repeat;
            padding-right: 15px;
        }

        .table a.asc {
            background-image: url("<?= base_url('static/dist/img/asc.png') ?>");
        }

        .table a.desc {
            background-image: url("<?= base_url('static/dist/img/desc.png') ?>");
        }

        .select2-selection__choice {
            color: #000 !important;
        }
        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            vertical-align: middle;
        }
    </style>
    <script>
        $(document).ready(function() {
            function customRange(input) {
                return {
                    minDate: (input.id.indexOf("_to") != -1 ? $('#' + input.id.replace("to", "from")).datepicker('getDate') : null),
                    maxDate: (input.id.indexOf("_from") != -1 ? $('#' + input.id.replace("from", "to")).datepicker('getDate') : null)
                };
            }
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                beforeShow: customRange
            });
            $('.timepicker').timepicker({
                timeFormat: 'HH:mm:ss',
                changeYear: true,
                changeMonth: true,
                showSecond: true,
                beforeShow: customRange
            });
            $('.secpicker').datetimepicker({
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss',
                changeMonth: true,
                changeYear: true,
                showSecond: true,
                beforeShow: customRange
            });

            $('#per_page').change(function() {
                $.post('<?= site_url("ajax/setPerPage") ?>', {
                    'per_page': $(this).val()
                }, function(data) {
                    if (data == 'done') {
                        location.reload();
                    } else {
                        alert('操作失败!');
                    }
                });
            });
        });
        function layer_open(url) {
            layer.open({
                type: 2,
                shadeClose: false,
                title: false,
                closeBtn: [0, true],
                shade: [0.8, '#000'],
                border: [1],
                offset: ['20px', ''],
                area: ['80%', '90%'],
                content: url
            });
        }
    </script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">
        <?php if ($sidebar) : ?>
            <header class="main-header">
                <!-- Logo -->
                <a href="<?= site_url() ?>" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini"><b>后台</b></span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg"><b>后台管理中心</b></span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="col-xs-5" style="margin:8px 0 0 5px;">
                        <select id="global_operator" class="form-control" multiple="multiple" style="height:20px;">
                            <?php foreach ($this->allow_operator as $key => $val) : ?>
                                <option value="<?= $key ?>" <?= $this->session->userdata('show_operator') !== null && in_array($key, $this->session->userdata('show_operator')) ? 'selected' : '' ?>><?= $val ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- Messages: style can be found in dropdown.less-->
                            <li class="dropdown messages-menu">
                                <a href="<?= site_url('user/online') ?>" class="dropdown-toggle">
                                    <i class="fa">在线会员</i>
                                    <span id="top_online" class="label label-success" style="display:none;">0</span>
                                </a>
                            </li>
                            <li class="dropdown messages-menu">
                                <a href="<?= site_url('user/index/create_time1/' . date('Y-m-d') . '/create_time2/' . date('Y-m-d')) ?>" class="dropdown-toggle">
                                    <i class="fa">今日注册会员</i>
                                    <span id="top_register" class="label label-success" style="display:none;">0</span>
                                </a>
                            </li>
                            <!-- Tasks: style can be found in dropdown.less -->
                            <li class="dropdown tasks-menu">
                                <a href="<?= site_url('recharge_order/index/status/0') ?>" class="dropdown-toggle">
                                    <i class="fa">充值</i>
                                    <span id="top_recharge" class="label label-danger" style="display:none;">0</span>
                                </a>
                            </li>
                            <li class="dropdown tasks-menu">
                                <a href="<?= site_url('user_withdraw/index/status/0') ?>" class="dropdown-toggle">
                                    <i class="fa">提现</i>
                                    <span id="top_withdraw" class="label label-danger" style="display:none;">0</span>
                                </a>
                            </li>
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="hidden-xs"><?= $this->session->userdata('username') ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="<?= base_url('static/dist/img/user2-160x160.jpg') ?>" class="img-circle" alt="User Image">
                                        <p><?= $this->session->userdata('username') ?></p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="#" class="btn btn-default btn-flat">密码修改</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="<?= site_url('login/logout') ?>" class="btn btn-default btn-flat">登出</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <script>
                //運營商
                $("#global_operator").select2();
                $("#global_operator").change(function() {
                    var global_operator = [];
                    $(this).find(":selected").each(function() {
                        global_operator[this.value] = this.value;
                    });
                    $.ajax({
                        type: "POST",
                        url: "<?= site_url('ajax/globalOperator') ?>",
                        data: {
                            operator: global_operator
                        },
                        dataType: "html",
                        success: function(result) {
                            if (result == 'done') {
                                location.reload();
                            }
                        }
                    });
                });
                //更新Top資訊
                var getTopMessage = function() {
                    var vid = document.getElementById("player_audio");
                    $.ajax({
                        type: "POST",
                        url: "<?= site_url('ajax/getTopMessage') ?>",
                        data: {},
                        cache: false,
                        dataType: "json",
                        success: function(result) {
                            if (result.player_audio == 1) {
                                vid.play();
                            }
                            $('#top_online').html(result.online)
                            $('#top_register').html(result.register);
                            $('#top_recharge').html(result.recharge);
                            $('#top_withdraw').html(result.withdraw);
                            result.online > 0 ? $('#top_online').show() : $('#top_online').hide();
                            result.register > 0 ? $('#top_register').show() : $('#top_register').hide();
                            result.recharge > 0 ? $('#top_recharge').show() : $('#top_recharge').hide();
                            result.withdraw > 0 ? $('#top_withdraw').show() : $('#top_withdraw').hide();
                        }
                    });
                };
                setInterval(getTopMessage, 5000);
                getTopMessage();
            </script>
            <!-- =============================================== -->

            <!-- Left side column. contains the sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu" data-widget="tree">
                        <?php foreach ($this->navList as $row) : ?>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array($row['url'], $this->allow_url)) : ?>
                                <li class="treeview <?= in_array($this->cur_url, $row['sub_urls']) ? 'active' : '' ?>">
                                    <a href="#">
                                        <i class="fa <?=$row['icon']?>"></i> <span><?= $row['name'] ?></span>
                                        <span class="pull-right-container">
                                            <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        <?php foreach ($row['sub'] as $arr) : ?>
                                            <?php if ($this->session->userdata('roleid') == 1 || in_array($arr['url'], $this->allow_url)) : ?>
                                                <li class="<?= $this->cur_url == $arr['url'] ? 'active' : '' ?>"><a href="<?= site_url($arr['url']) ?>"><i class="fa fa-circle-o"></i> <?= $arr['name'] ?></a></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- =============================================== -->

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1 style="font-size:20px;"><?= $this->title ?></h1>
                    <ol class="breadcrumb">
                        <li><a href="<?= site_url() ?>"><i class="fa fa-dashboard"></i> 首页</a></li>
                        <?php foreach ($this->breadcrumb as $row) : ?>
                            <li><a href="<?= site_url($row['url']) ?>"><?= $row['name'] ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <?= $content_for_layout ?>
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version:</b> <?= $this->version ?>
                </div>
                <strong>Page rendered in {elapsed_time} second and used {memory_usage} memory.</strong>
            </footer>
            <audio id="player_audio" controls preload hidden>
                <source src="<?= base_url('static/prompt.mp3') ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        <?php else : ?>
            <!-- Content Wrapper. Contains page content -->
            <div style="background:#ffffff;">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1><?= $this->title ?></h1>
                    <ol class="breadcrumb">
                        <li><a><i class="fa fa-dashboard"></i> 首页</a></li>
                        <?php foreach ($this->breadcrumb as $row) : ?>
                            <li><a><?= $row['name'] ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </section>
                <!-- Main content -->
                <section class="content">
                    <?= $content_for_layout ?>
                </section>
                <!-- /.content -->
            </div>
        <?php endif; ?>
    </div>
    <!-- ./wrapper -->
    <!-- Bootstrap 3.3.7 -->
    <script src="<?= base_url('static/bower_components/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
    <!-- SlimScroll -->
    <script src="<?= base_url('static/bower_components/jquery-slimscroll/jquery.slimscroll.min.js') ?>"></script>
    <!-- FastClick -->
    <script src="<?= base_url('static/bower_components/fastclick/lib/fastclick.js') ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?= base_url('static/dist/js/adminlte.min.js') ?>"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="<?= base_url('static/dist/js/demo.js') ?>"></script>
    <script>
        $(document).ready(function() {
            $('.sidebar-menu').tree();
        })
    </script>
</body>

</html>