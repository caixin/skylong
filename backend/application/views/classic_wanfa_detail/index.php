<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($where['wanfa_pid']) ? $where['wanfa_pid'] : 0 ?>,<?= isset($where['wanfa_id']) ? $where['wanfa_id'] : 0 ?>">
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
                <label>玩法类型</label>
                <select id="wanfa_pid" name="wanfa_pid" class="form-control">
                    <option value="">请选择</option>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>玩法</label>
                <select id="wanfa_id" name="wanfa_id" class="form-control">
                    <option value="">请选择</option>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>玩法值</label>
                <input type="text" name="values" class="form-control" placeholder="请输入..." value="<?= isset($where['values']) ? $where['values'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>满盘賠率</label>
                <input type="text" name="odds" class="form-control" placeholder="请输入..." value="<?= isset($where['odds']) ? $where['odds'] : '' ?>">
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
                    <th nowrap><?= sort_title('id', '编号', $this->cur_url, $order, $where) ?></th>
                    <th nowrap><?= sort_title('lottery_type_id', '彩种类别', $this->cur_url, $order, $where) ?></th>
                    <th nowrap><?= sort_title('wanfa_id', '玩法類型', $this->cur_url, $order, $where) ?></th>
                    <th nowrap><?= sort_title('values', '玩法值', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('values_sup', '輔助玩法值', $this->cur_url, $order, $where) ?></th>
                    <th nowrap><?= sort_title('max_number', '選號上限', $this->cur_url, $order, $where) ?></th>
                    <th nowrap><?= sort_title('odds', '满盘賠率', $this->cur_url, $order, $where) ?></th>
                    <th nowrap><?= sort_title('odds_special', '特殊賠率', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('line_a_profit', 'A盘获利', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('line_a_special', 'A盘特殊', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('qishu_max_money', '单期最大限额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_max_money', '单笔最大限额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bet_min_money', '最小下注额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('sort', '排序', $this->cur_url, $order, $where) ?></th>
                    <th width="90"><?= sort_title('update_time', '修改日期', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_by', '最後修改者', $this->cur_url, $order, $where) ?></th>
                    <th width="130">
                        <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/create", $this->allow_url)) : ?>
                            <button type="button" class="btn btn-primary" onclick="add()">添加</button>
                        <?php endif; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $lottery_type[$row['lottery_type_id']] ?></td>
                        <td><?= isset($parent_wanfa[$row['pid']]) ? $parent_wanfa[$row['pid']] : '' ?> → <?= $row['name'] ?></td>
                        <td><?= $row['values'] ?></td>
                        <td style="word-break: break-all;max-width:200px;"><?= $row['values_sup'] ?></td>
                        <td><?= $row['max_number'] ?></td>
                        <td><?= $row['odds'] ?></td>
                        <td><?= $row['odds_special'] ?></td>
                        <td><?= $row['line_a_profit'] ?>%</td>
                        <td><?= $row['line_a_special'] ?>%</td>
                        <td><?= $row['qishu_max_money'] ?></td>
                        <td><?= $row['bet_max_money'] ?></td>
                        <td><?= $row['bet_min_money'] ?></td>
                        <td><?= $row['sort'] ?></td>
                        <td><?= $row['update_time'] ?></td>
                        <td><?= $row['update_by'] ?></td>
                        <td>
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
    function add() {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/create") ?>'
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

    $(function() {
        // 判斷是否有預設值
        var defaultValue = false;
        if (0 < $.trim($('#fullIdPath').val()).length) {
            $fullIdPath = $('#fullIdPath').val().split(',');
            defaultValue = true;
        }

        $('#lottery_type_id').change(function() {
            $('#wanfa_pid').empty().append("<option value=''>请选择</option>");
            $('#wanfa_id').empty().append("<option value=''>请选择</option>");
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getClassicWanfa") ?>',
                data: {
                    lottery_type_id: $('#lottery_type_id').val()
                },
                dataType: "json",
                success: function(result) {
                    for (var i = 0; i < result.length; i++) {
                        $("#wanfa_pid").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue && $fullIdPath[0] != 0) {
                        $('#wanfa_pid').val($fullIdPath[0]).change();
                    }
                    if ($('#wanfa_pid').val() == null) {
                        $('#wanfa_pid').val('');
                    }
                }
            });
        });

        $('#wanfa_pid').change(function() {
            var pid = $('#wanfa_pid').val();
            $('#wanfa_id').empty().append("<option value=''>请选择</option>");
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getClassicWanfa") ?>',
                data: {
                    lottery_type_id: $('#lottery_type_id').val(),
                    pid: pid
                },
                dataType: "json",
                success: function(result) {
                    for (var i = 0; i < result.length; i++) {
                        $("#wanfa_id").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue) {
                        $('#wanfa_id').val($fullIdPath[1]);
                    }
                    if ($('#wanfa_id').val() == null) {
                        $('#wanfa_id').val('');
                    }
                }
            });
        });
        $('#lottery_type_id').change();
    });
</script>