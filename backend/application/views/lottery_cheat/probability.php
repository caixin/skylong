<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <!-- Custom Tabs -->
    <div class="nav-tabs-custom">
        <?php $this->load->view("{$this->router->class}/_tabs", array('type'=>3)); ?>
        <div class="tab-content">
            <div class="box box-success">
                <div class="box-header">
                    <form method="post" action="">
                        <div class="col-xs-1" style="width:auto;">
                            <label>运营商名称</label>
                            <select name="operator_id" class="form-control">
                                <?php foreach ($operator as $key => $val): ?>
                                    <option value="<?= $key ?>" <?= isset($where['operator_id']) && $where['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xs-1" style="width:auto;">
                            <label>彩种名称</label>
                            <select name="lottery_id" class="form-control">
                                <option value="">全部</option>
                                <?php foreach ($lottery as $key => $val) : ?>
                                    <option value="<?= $key ?>" <?= isset($where['lottery_id']) && $where['lottery_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xs-1" style="width:auto;">
                            <label>状态</label>
                            <select name="status" class="form-control">
                                <option value="">请选择</option>
                                <?php foreach (Ettm_lottery_cheat_model::$statusList as $key => $val) : ?>
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
                                <th><?= sort_title('lottery_id', '彩种名称', $this->cur_url, $order, $where) ?></th>
                                <th><?= sort_title('starttime', '起始时间', $this->cur_url, $order, $where) ?></th>
                                <th><?= sort_title('endtime', '结束时间', $this->cur_url, $order, $where) ?></th>
                                <th><?= sort_title('percent', '百分比', $this->cur_url, $order, $where) ?></th>
                                <th><?= sort_title('status', '状态', $this->cur_url, $order, $where) ?></th>
                                <th><?= sort_title('create_time', '添加日期', $this->cur_url, $order, $where) ?></th>
                                <th><?= sort_title('create_by', '添加者', $this->cur_url, $order, $where) ?></th>
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
                                    <td><?= $lottery[$row['lottery_id']] ?></td>
                                    <td><?= $row['starttime'] ?></td>
                                    <td><?= $row['endtime'] ?></td>
                                    <td><?= $row['percent'] ?></td>
                                    <td><?= Ettm_lottery_cheat_model::$statusList[$row['status']] ?></td>
                                    <td><?= $row['create_time'] ?></td>
                                    <td><?= $row['create_by'] ?></td>
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
        </div>
        <!-- /.tab-content -->
    </div>
    <!-- nav-tabs-custom -->
</div>
<script>
    //添加
    function add(type) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/create/$where[operator_id]/3") ?>'
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
</script>