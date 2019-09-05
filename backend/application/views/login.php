<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>后台管理中心 | 登入</title>
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
    <!-- iCheck -->
    <link rel="stylesheet" href="<?= base_url('static/plugins/iCheck/square/blue.css') ?>">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <style>
        .login-page {
            background: none;
            background-image: url(<?= base_url("static/dist/img/login_bg_0" . rand(1, 8) . ".jpg") ?>);
        }
    </style>
</head>

<body class="hold-transition login-page" scroll="no" background="">
    <div class="login-box">
        <div class="login-logo">&nbsp;</div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p class="login-box-msg"><b>后台管理中心</b></p>
            <form action="" method="post">
                <div class="form-group has-feedback">
                    <input type="text" class="form-control" name="mobile" id="mobile" placeholder="手机号码">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" name="password" id="password" placeholder="密码">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback">
                    <input type="password" class="form-control" name="otp" placeholder="OTP">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-4">
                        <input type="button" onclick="produceOtp();" class="btn btn-primary btn-block btn-flat" id="produce_otp" value="产生OTP" <?= !empty($_COOKIE['otpCountdown']) ? 'disabled' : '' ?>>
                    </div>
                    <!-- /.col -->
                    <div class="col-xs-8">
                        <input type="submit" class="btn btn-primary btn-block btn-flat" value="登入">
                    </div>
                    <!-- /.col -->
                </div>
            </form>
            <!-- /.social-auth-links -->
        </div>
        <?php if (validation_errors() || $this->session->flashdata('message')) : ?>
            <div class="alert alert-danger alert-dismissible" style="margin-top:10px;">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> 错误!</h4>
                <?= validation_errors() ?>
                <?= $this->session->flashdata('message') ?>
            </div>
        <?php endif; ?>
        <!-- /.login-box-body -->
    </div>
    <!-- /.login-box -->

    <!-- jQuery 3 -->
    <script src="<?= base_url('static/bower_components/jquery/dist/jquery.min.js') ?>"></script>
    <!-- jQuery Cookie 1.4.1 -->
    <script src="<?= base_url('static/bower_components/jquery/dist/jquery.cookie.js') ?>"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="<?= base_url('static/bower_components/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
    <!-- iCheck -->
    <script src="<?= base_url('static/plugins/iCheck/icheck.min.js') ?>"></script>
    <script>
        $(function() {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' /* optional */
            });

            if ($.cookie("otpCountdown") !== undefined) {
                timeCountdown = setInterval("startCountdown()", 1000);
            }
            console.log($.cookie("otpCountdown"));
        });

        function startCountdown() {
            var i = 60 - (getUnixTime() - $.cookie("otpCountdown"));
            if (i > 0) {
                $("#produce_otp").val('请等' + i + '秒');
                $("#produce_otp").attr('disabled', true);
            } else {
                $("#produce_otp").val("产生OTP");
                $("#produce_otp").removeAttr("disabled");
                clearInterval(timeCountdown);
                $.removeCookie("otpCountdown");
            }
            console.log(i);
        }

        function getUnixTime() {
            return Date.parse(new Date()) / 1000;
        }

        function produceOtp() {
            var mobile = $("#mobile").val();
            $.post('<?= site_url("{$this->router->class}/produceOtp") ?>', {
                'mobile': mobile
            }, function(data) {
                if (data == 'done') {
                    location.reload();
                } else {
                    alert(data);
                }
            });
        }
    </script>
</body>

</html>