<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" id="fullIdPath" value="<?= isset($where['lottery_type_id']) ? $where['lottery_type_id'] : -1 ?>,<?= isset($where['lottery_id']) ? $where['lottery_id'] : -1 ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>营运商</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['operator_id']) && $where['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>会员分层</label>
                <select name="user_group_id" class="form-control">
                    <option value="">全部</option>
                    <?php foreach ($user_group as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['user_group_id']) && $where['user_group_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>返水类型</label>
                <select name="type" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (user_rakeback_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>玩法类别</label>
                <select id="category" name="category" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (user_rakeback_model::$categoryList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['category']) && $where['category'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>彩种大类</label>
                <select id="lottery_type_id" name="lottery_type_id" class="form-control">
                    <option value="">请选择</option>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>彩种</label>
                <select id="lottery_id" name="lottery_id" class="form-control">
                    <option value="">请选择</option>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>&nbsp;</label>
                <button type="submit" class="form-control btn btn-primary">查询</button>
            </div>
        </form>
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
                    <th><?= sort_title('operator_id', '运营商', $this->cur_url, $order, $where) ?></th>
                    <th width="120"><?= sort_title('user_group_id', '会员分层', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('type', '返水类型', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('category', '玩法类别', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_type_id', '彩种大类', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('lottery_id', '彩种', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('start_money', '起算金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('rakeback_per', '返水比例', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('rakeback_max', '返水上限', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_time', '修改日期', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_by', '修改者', $this->cur_url, $order, $where) ?></th>
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
                        <td><?= $operator[$row['operator_id']] ?></td>
                        <td><?= $user_group[$row['user_group_id']] ?></td>
                        <td><?= user_rakeback_model::$typeList[$row['type']] ?></td>
                        <td><?= user_rakeback_model::$categoryList[$row['category']] ?></td>
                        <td><?= $row['lottery_type_name'] ?></td>
                        <td><?= $row['lottery_name'] ?></td>
                        <td><?= $row['start_money'] ?></td>
                        <td><?= $row['rakeback_per'] ?>%</td>
                        <td><?= $row['rakeback_max'] ?></td>
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
            content: '<?= site_url("{$this->router->class}/create/$where[operator_id]") ?>/' + id
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

    $(function() {
        // 判斷是否有預設值
        var defaultValue = false;
        if (0 < $.trim($('#fullIdPath').val()).length) {
            $fullIdPath = $('#fullIdPath').val().split(',');
            defaultValue = true;
        }

        $('#category').change(function() {
            $('#lottery_type_id').empty().append("<option value=''>请选择</option>");
            $('#lottery_id').empty().append("<option value=''>请选择</option>");
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getLotteryType") ?>',
                data: {
                    category: $('#category').val()
                },
                dataType: "json",
                success: function(result) {
                    if (result.length > 0) {
                        $('#lottery_type_id').append("<option value='0'>全部</option>");
                    }
                    for (var i = 0; i < result.length; i++) {
                        $("#lottery_type_id").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue && $fullIdPath[0] != 0) {
                        $('#lottery_type_id').val($fullIdPath[0]).change();
                    }
                    if ($('#lottery_type_id').val() == null) {
                        $('#lottery_type_id').val('');
                    }
                }
            });
        });

        $('#lottery_type_id').change(function() {
            $('#lottery_id').empty().append("<option value=''>请选择</option>");
            $.ajax({
                type: "POST",
                url: '<?= site_url("ajax/getLottery") ?>',
                data: {
                    category: $('#category').val(),
                    typeid: $('#lottery_type_id').val()
                },
                dataType: "json",
                success: function(result) {
                    if (result.length > 0) {
                        $('#lottery_id').append("<option value='0'>全部</option>");
                    }
                    for (var i = 0; i < result.length; i++) {
                        $("#lottery_id").append("<option value='" + result[i]['id'] + "'>" + result[i]['name'] + "</option>");
                    }
                    // 設定預設選項
                    if (defaultValue) {
                        $('#lottery_id').val($fullIdPath[1]);
                    }
                    if ($('#lottery_id').val() == null) {
                        $('#lottery_id').val('');
                    }
                }
            });
        });
        $('#category').change();
    });
</script>