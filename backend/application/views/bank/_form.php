<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="">
            <div class="form-group <?= form_error('name') ? 'has-error' : '' ?>">
                <label>银行名称</label>
                <input type="text" name="name" class="form-control" placeholder="Enter ..." value="<?= isset($row['name']) ? $row['name'] : '' ?>">
                <?= form_error('name', '<span class="help-block">', '</span>') ?>
            </div>
            <div class="form-group <?= form_error('image_url') ? 'has-error' : '' ?>">
                <label>银行图片</label>
                <button type="button" id="upload" class="btn btn-primary">上传图片</button>
                <button type="button" id="delete" class="btn btn-primary">删除图片</button>
                <br>
                <span class="error" id="error_image"></span>
                <input type="hidden" id="image_url" name="image_url" value="<?= isset($row['image_url']) ? $row['image_url'] : '' ?>">
                <img id="img" src="<?= isset($row['image_url']) ? $this->site_config['image_path'] . $row['image_url'] : ''; ?>" width="100">
                <?= form_error('image_url', '<span class="help-block">', '</span>') ?>
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
        if ($('[name="image_url"]').val() == '') {
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
            PostInit: function() {},

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
                    $('#image_url').val(response.filelink);
                    toggle_image();
                } else {
                    $("#error_image").html(response.message);
                }
            }
        }
    });

    uploader.init();

    $('#delete').click(function() {
        $('[name="image_url"]').attr('value', '');
        $('#img').attr('src', '');
        toggle_image();
    });

    toggle_image();
</script>