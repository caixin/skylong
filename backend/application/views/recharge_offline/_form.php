<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('user_group_ids') ? 'has-error' : '' ?>">
                <label>用户分层</label>
                <select name="user_group_ids[]" class="form-control select2" multiple="multiple">
                    <?php foreach ($user_group as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['user_group_ids']) && in_array($key, $row['user_group_ids']) ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('user_group_ids', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('channel') ? 'has-error' : '' ?>">
                <label>充值渠道</label>
                <select id="channel" name="channel" class="form-control">
                    <?php foreach (recharge_offline_model::$channelList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['channel']) && $row['channel'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('channel', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('nickname') ? 'has-error' : '' ?>">
                <label>昵称</label>
                <input type="text" name="nickname" class="form-control" placeholder="Enter ..." value="<?= isset($row['nickname']) ? $row['nickname'] : '' ?>">
                <?= form_error('nickname', '<span class="help-block">', '</span>') ?>
            </div>
            <div id="bank_name" class="form-group <?= form_error('bank_id') ? 'has-error' : '' ?>">
                <label>银行名称</label>
                <select id="bank_id" name="bank_id" class="form-control">
                    <?php foreach ($bank as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['bank_id']) && $row['bank_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('bank_id', '<span class="help-block">', '</span>') ?>
            </div>
            <div id="qrcode_img" class="form-group <?= form_error('qrcode') ? 'has-error' : '' ?>">
                <label>二维码图片</label>
                <button type="button" id="upload" class="btn btn-primary">上传图片</button>
                <button type="button" id="delete" class="btn btn-primary">删除图片</button>
                <br>
                <span class="error" id="error_image"></span>
                <input type="hidden" id="qrcode" name="qrcode" value="<?= isset($row['qrcode']) ? $row['qrcode'] : '' ?>">
                <img id="img" src="<?= isset($row['qrcode']) ? $this->site_config['image_path'] . $row['qrcode'] : ''; ?>" width="100">
                <?= form_error('qrcode', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('account') ? 'has-error' : '' ?>">
                <label>账号</label>
                <input type="text" name="account" class="form-control" placeholder="Enter ..." value="<?= isset($row['account']) ? $row['account'] : '' ?>">
                <?= form_error('account', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('handsel_percent') ? 'has-error' : '' ?>">
                <label>赠送彩金比例</label>
                <input type="number" name="handsel_percent" class="form-control" placeholder="Enter ..." value="<?= isset($row['handsel_percent']) ? $row['handsel_percent'] : '' ?>" step="0.01">
                <?= form_error('handsel_percent', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('handsel_max') ? 'has-error' : '' ?>">
                <label>赠送彩金上限</label>
                <input type="number" name="handsel_max" class="form-control" placeholder="Enter ..." value="<?= isset($row['handsel_max']) ? $row['handsel_max'] : '' ?>">
                <?= form_error('handsel_max', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('multiple') ? 'has-error' : '' ?>">
                <label>打码量倍数</label>
                <input type="number" name="multiple" class="form-control" placeholder="Enter ..." value="<?= isset($row['multiple']) ? $row['multiple'] : '' ?>">
                <?= form_error('multiple', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('min_money') ? 'has-error' : '' ?>">
                <label>单笔最小限额</label>
                <input type="number" name="min_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['min_money']) ? $row['min_money'] : '' ?>" step="0.01">
                <?= form_error('min_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('max_money') ? 'has-error' : '' ?>">
                <label>单笔最大限额</label>
                <input type="number" name="max_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['max_money']) ? $row['max_money'] : '' ?>" step="0.01">
                <?= form_error('max_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('day_max_money') ? 'has-error' : '' ?>">
                <label>单日最大限额</label>
                <input type="number" name="day_max_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['day_max_money']) ? $row['day_max_money'] : '' ?>" step="0.01">
                <?= form_error('day_max_money', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
                <label>状态</label>
                <select name="status" class="form-control">
                    <?php foreach (recharge_offline_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
                <?= form_error('status', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
                <label>排序</label>
                <input type="text" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
                <?= form_error('sort', '<span class="help-block">', '</span>') ?>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<div id="plupload_ani" style="display:none;"></div>
<script src="<?= base_url("static/plugins/plupload/plupload.full.min.js") ?>"></script>
<script>
    $('.select2').select2();

    function toggle_image() {
        if ($('[name="qrcode"]').val() == '') {
            $('#delete, #img').hide();
            $('#upload, #error_image').show();
        } else {
            $('#delete, #img').show();
            $('#upload, #error_image').hide();
        }
    }

    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: 'upload', // you can pass in id...
        container: 'plupload_ani', // ... or DOM Element itself
        max_file_size: '1mb',
        multi_selection: false,
        url: '<?= site_url('ajax/image_upload/' . $this->router->class) ?>',
        flash_swf_url: '<?= base_url("static/plugins/plupload/Moxie.swf") ?>',
        silverlight_xap_url: '<?= base_url("static/plugins/plupload/Moxie.xap") ?>',

        filters: {
            max_file_size: '1mb',
            mime_types: [{
                    title: "Image files",
                    extensions: "jpg,gif,png"
                },
                {
                    title: "Zip files",
                    extensions: "zip"
                }
            ]
        },

        init: {
            PostInit: function() {

            },

            FilesAdded: function(up, files) {
                up.refresh(); // Reposition Flash/Silverlight
                setTimeout(function() {
                    uploader.start();
                }, 1000); // auto start
            },

            UploadProgress: function(up, file) {
                $('#error_image').html(file.percent + '%');
            },

            Error: function(up, err) {
                $('#error_image').html(err.code + ": " + err.message);
            },

            FileUploaded: function(up, file, response) {
                var go_response = response;
                var response = $.parseJSON(go_response.response);
                if (response.status == '1') {
                    $('#img').attr('src', '<?= $this->site_config['image_path'] ?>' + response.filelink);
                    $('#qrcode').val(response.filelink);
                    toggle_image();
                } else {
                    $("#error_image").html(response.message);
                }
            }
        }
    });

    uploader.init();

    $('#delete').click(function() {
        $('[name="qrcode"]').attr('value', '');
        $('#img').attr('src', '');
        toggle_image();
    });

    toggle_image();

    $('#channel').change(function() {
        if ($(this).val() == 1) {
            $('#bank_name').show();
            $('#qrcode_img').hide();
        } else {
            $('#bank_name').hide();
            $('#qrcode_img').show();
        }
    });
    $('#channel').change();
</script>