<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>用户名称</label>
                <input type="text" name="username" class="form-control" placeholder="请输入..." value="<?= isset($where['username']) ? $where['username'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>手机号码</label>
                <input type="text" name="mobile" class="form-control" placeholder="请输入..." value="<?= isset($where['mobile']) ? $where['mobile'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>是否为代理帐号</label>
                <select name="is_agent" class="form-control">
                    <option value="">全部</option>
                    <?php foreach (admin_model::$is_agentList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['is_agent']) && $where['is_agent'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>添加日期</label>
                <div class="input-group">
                    <input type="text" id="create_time_from" name="create_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['create_time1']) ? $where['create_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="create_time_to" name="create_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['create_time2']) ? $where['create_time2'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1">
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
                    <th><?= sort_title('username', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('mobile', '手机号码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('roleid', '角色群组', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('is_agent', '代理帐号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('uid', '代理UID', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '添加日期', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('login_time', '登录日期', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('otp_check', 'OTP', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('status', '状态', $this->cur_url, $order, $where) ?></th>
                    <th>
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
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['mobile'] ?></td>
                        <td><?= $role[$row['roleid']] ?></td>
                        <td><?= admin_model::$is_agentList[$row['is_agent']] ?></td>
                        <td><?= $row['is_agent'] == 1 ? "<a href='".site_url("user/index/user_name/$row[user_name]")."'>$row[uid]</a>":'' ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td><?= $row['login_time'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                <button type="button" id="otp_check1_<?= $row['id'] ?>" class="btn <?= $row['otp_check'] == 1 ? 'btn-info' : 'btn-default' ?>" onclick="switch_row(<?= $row['id'] ?>,'otp_check',1)">
                                    <?= admin_model::$otp_checkList[1] ?>
                                </button>
                                <button type="button" id="otp_check0_<?= $row['id'] ?>" class="btn <?= $row['otp_check'] == 0 ? '' : 'btn-default' ?>" onclick="switch_row(<?= $row['id'] ?>,'otp_check',0)">
                                    <?= admin_model::$otp_checkList[0] ?>
                                </button>
                            <?php else : ?>
                                <?= admin_model::$otp_checkList[$row['otp_check']] ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                <button type="button" id="status1_<?= $row['id'] ?>" class="btn <?= $row['status'] == 1 ? 'btn-info' : 'btn-default' ?>" onclick="switch_row(<?= $row['id'] ?>,'status',1)">
                                    <?= admin_model::$statusList[1] ?>
                                </button>
                                <button type="button" id="status0_<?= $row['id'] ?>" class="btn <?= $row['status'] == 0 ? '' : 'btn-default' ?>" onclick="switch_row(<?= $row['id'] ?>,'status',0)">
                                    <?= admin_model::$statusList[0] ?>
                                </button>
                            <?php else : ?>
                                <?= admin_model::$statusList[$row['status']] ?>
                            <?php endif; ?>
                        </td>
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
    //删除
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
    //开关
    function switch_row(id, column, value) {
        if (value == 1) {
            $('#' + column + '1_' + id).removeClass('btn-default').addClass('btn-info');
            $('#' + column + '0_' + id).addClass('btn-default');
        } else {
            $('#' + column + '1_' + id).removeClass('btn-info').addClass('btn-default');
            $('#' + column + '0_' + id).removeClass('btn-default');
        }
        $.post('<?= site_url("{$this->router->class}/edit") ?>/' + id, {
            'column': column,
            'value': value
        });
    }
</script>