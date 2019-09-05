<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>会员分层</label>
                <select name="user_group_ids" class="form-control">
                    <option value="">全部</option>
                    <?php foreach ($user_group as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['user_group_ids']) && $where['user_group_ids'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>渠道</label>
                <select name="channel" class="form-control">
                    <option value="">全部</option>
                    <?php foreach (recharge_offline_model::$channelList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['channel']) && $where['channel'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>状态</label>
                <select name="status" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (recharge_offline_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['status']) && $where['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
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
                    <th width="120"><?= sort_title('user_group_ids', '会员分层', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('channel', '渠道', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('nickname', '昵称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('bank_name', '银行名称', $this->cur_url, $order, $where) ?></th>
                    <th>图片</th>
                    <th><?= sort_title('account', '账号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('handsel_percent', '彩金比例', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('handsel_max', '彩金上限', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('multiple', '打码量', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('min_money', '单笔限额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('day_max_money', '单日限额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('status', '状态', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('sort', '排序', $this->cur_url, $order, $where) ?></th>
                    <th width="100"><?= sort_title('update_time', '修改日期', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('update_by', '修改者', $this->cur_url, $order, $where) ?></th>
                    <th width="140">
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
                        <td><?= $row['user_group'] ?></td>
                        <td><?= recharge_offline_model::$channelList[$row['channel']] ?></td>
                        <td><?= $row['nickname'] ?></td>
                        <td><?= $row['bank_name'] ?></td>
                        <td>
                            <?php if ($row['channel'] == 1) : ?>
                                <img src="<?= $row['bank_logo'] != '' ? $this->site_config['image_path'] . $row['bank_logo'] : '' ?>" width="100">
                            <?php else : ?>
                                <img src="<?= $row['qrcode'] != '' ? $this->site_config['image_path'] . $row['qrcode'] : '' ?>" width="100">
                            <?php endif; ?>
                        </td>
                        <td><?= $row['account'] ?></td>
                        <td><?= $row['handsel_percent'] ?>%</td>
                        <td><?= $row['handsel_max'] ?></td>
                        <td><?= $row['multiple'] ?></td>
                        <td><?= $row['min_money'] ?>~<?= $row['max_money'] ?></td>
                        <td><?= $row['day_max_money'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                <button type="button" id="status1_<?= $row['id'] ?>" class="btn <?= $row['status'] == 1 ? 'btn-info' : 'btn-default' ?>" onclick="status_row(<?= $row['id'] ?>,1)"><?= recharge_offline_model::$statusList[1] ?></button>
                                <button type="button" id="status0_<?= $row['id'] ?>" class="btn <?= $row['status'] == 0 ? '' : 'btn-default' ?>" onclick="status_row(<?= $row['id'] ?>,0)"><?= recharge_offline_model::$statusList[0] ?></button>
                            <?php else : ?>
                                <?= recharge_offline_model::$statusList[$row['status']] ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['sort'] ?></td>
                        <td><?= $row['update_time'] ?></td>
                        <td><?= $row['update_by'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/create", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" style="margin-bottom: 3px;" onclick="add(<?= $row['id'] ?>)">复制新增</button>
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
    //关闭
    function status_row(id, status) {
        if (status == 1) {
            $('#status1_' + id).removeClass('btn-default').addClass('btn-info');
            $('#status0_' + id).addClass('btn-default');
        } else {
            $('#status1_' + id).removeClass('btn-info').addClass('btn-default');
            $('#status0_' + id).removeClass('btn-default');
        }
        $.post('<?= site_url("{$this->router->class}/edit") ?>/' + id, {
            'status': status
        });
    }
</script>