<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($where['pid']) ? $where['pid'] : '' ?>" />
            <div class="col-xs-1" style="width:auto;">
                <label>彩种类别</label>
                <select id="lottery_type_id" name="lottery_type_id" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach ($lottery_type as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['lottery_type_id']) && $where['lottery_type_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>上层玩法</label>
                <select id="pid" name="pid" class="form-control">
                    <option value="0">只显示第一层</option>
                    <option value="-1">只显示第二层</option>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>&nbsp;</label>
                <button type="submit" class="form-control btn btn-primary">查询</button>
            </div>
        </form>
        <div class="col-xs-1" style="width:auto;float:right;">
            <label>&nbsp;</label>
            <button type="button" id="import" class="form-control btn btn-primary">汇入</button>
            <span id="error_import"></span>
        </div>
        <div class="col-xs-1" style="width:auto;float:right;">
            <label>&nbsp;</label>
            <a href="<?= site_url("{$this->router->class}/export/$params_uri") ?>" class="form-control btn btn-primary">汇出</a>
        </div>
    </div>
    <div class="box-header">
        <label for="per_page">显示笔数:</label>
        <input type="test" id="per_page" value="<?= $this->per_page ?>" size="1">
        <h5 class="box-title" style="font-size: 14px;"><b>总计:</b> <?= $total ?></h5>
        <?= $this->pagination->create_links() ?>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= sort_title('id', '编号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_type_id', '彩种类别', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('pid', '上层玩法', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('name', '玩法名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('odds', '满盘赔率', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('line_a_profit', 'A盘获利(%)', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('max_return', '最大返点', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('max_bet_number', '最大注数', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('max_bet_money', '最大投注额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('key_word', 'Keyword', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('sort', '排序', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_time', '修改日期', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_by', '最後修改者', $this->cur_url, $order, $where) ?></th>
                    <th width="220">
                        <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/create", $this->allow_url)) : ?>
                            <button type="button" class="btn btn-primary" onclick="add(0)">添加</button>
                        <?php endif; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $lottery_type[$row['lottery_type_id']] ?></td>
                        <td><?= isset($parent_wanfa[$row['pid']]) ? $parent_wanfa[$row['pid']] : '无' ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['odds'] ?></td>
                        <td><?= $row['line_a_profit'] ?>%</td>
                        <td><?= $row['max_return'] ?></td>
                        <td><?= $row['max_bet_number'] ?></td>
                        <td><?= $row['max_bet_money'] ?></td>
                        <td><?= $row['key_word'] ?></td>
                        <td><?= $row['sort'] ?></td>
                        <td><?= $row['update_time'] ?></td>
                        <td><?= $row['update_by'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/create", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="add(<?= $row['id'] ?>)">复制新增</button>
                            <?php endif; ?>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="edit(<?= $row['id'] ?>)">编辑</button>
                            <?php endif; ?>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/delete", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="delete_row(<?= $row['id'] ?>)">删除</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
        <?= $this->pagination->create_links() ?>
    </div>
</div>
<div id="plupload_ani" style="display:none;"></div>
<script src="<?= base_url("static/plugins/plupload/plupload.full.min.js") ?>"></script>
<script>
    //添加
    function add(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/create") ?>/' + id
        });
    }
    //编辑
    function edit(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/edit") ?>/' + id
        });
    }
    //刪除
    function delete_row(id) {
        if (confirm('您确定要删除吗?')) {
            $.post('<?= site_url("{$this->router->class}/delete") ?>', {
                'id': id
            }, function(data) {
                if (data == 'done') {
                    location.reload();
                } else {
                    alert('操作失败!');
                }
            });
        }
    }
    //匯入
    var uploader_ani = new plupload.Uploader({
        runtimes: 'html5,flash',
        browse_button: 'import',
        container: 'plupload_ani',
        max_file_size: '100mb',
        multi_selection: false,
        url: '<?= site_url($this->router->class . "/import") ?>',
        flash_swf_url: '<?= base_url("static/plugins/plupload/plupload.flash.swf") ?>'
    });
    uploader_ani.bind('Init', function(up, params) {});
    uploader_ani.init();
    uploader_ani.bind('FilesAdded', function(up, files) {
        up.refresh(); // Reposition Flash/Silverlight
        setTimeout(function() {
            uploader_ani.start();
        }, 1000); // auto start
    });
    uploader_ani.bind('UploadProgress', function(up, file) {
        loadding = layer.load();
        $('#error_import').html('检测中(' + file.percent + '%)');
    });
    uploader_ani.bind('Error', function(up, err) {
        $('#error_import').html(err.message);
        layer.close(loadding);
        up.refresh(); // Reposition Flash/Silverlight
    });
    uploader_ani.bind('FileUploaded', function(up, file, response) {
        var go_response = response;
        var response = $.parseJSON(go_response.response);
        if (response.status == '1') {
            $('#error_import').html(response.message);
            location.reload();
        } else {
            $('#error_import').html(response.message);
        }
        layer.close(loadding);
    });

    $('#lottery_type_id').change(function() {
        // 判斷是否有預設值
        var defaultValue = false;
        if (0 < $.trim($('#fullIdPath').val()).length) {
            $fullIdPath = $('#fullIdPath').val().split(',');
            defaultValue = true;
        }
        $('#pid').empty().append("<option value=''>请选择</option><option value='0'>只显示第一层</option><option value='-1'>只显示第二层</option>");
        $.ajax({
            type: "POST",
            url: '<?= site_url("ajax/getOfficialWanfa") ?>',
            data: {
                lottery_type_id: $('#lottery_type_id').val()
            },
            dataType: "json",
            success: function(result) {
                for (var i = 0; i < result.length; i++) {
                    $("#pid").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                }
                // 設定預設選項
                if (defaultValue) {
                    $('#pid').val($fullIdPath[0]);
                }
                if ($('#pid').val() == null) {
                    $('#pid').val('');
                }
            }
        });
    });
    $('#lottery_type_id').change();
</script>