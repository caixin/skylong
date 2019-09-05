<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>代理名称</label>
                <select name="agent_id" class="form-control">
                    <option value="">全部</option>
                    <?php foreach ($agent as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['agent_id']) && $where['agent_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户名称</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>邀请码</label>
                <input type="text" name="code" class="form-control" placeholder="请输入..." value="<?= isset($where['code']) ? $where['code'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>类型</label>
                <select name="type" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (agent_code_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
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
                    <th><?= sort_title('agent_id', '代理名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('agent_pid', '上层用户', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('uid', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('code', '邀请码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('type', '类型', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('level', '层级', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('note', '备注', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '添加时间', $this->cur_url, $order, $where) ?></th>
                    <th width="150"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['agent_id'] == 0 ? '超级用户':$agent[$row['agent_id']] ?></td>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['agent_pid'] ?>);"><?= $row['pname'] ?></a></td>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);"><?= $row['user_name'] ?></a></td>
                        <td><?= $row['code'] ?></td>
                        <td><?= agent_code_model::$typeList[$row['type']] ?></td>
                        <td><?= $row['level'] ?></td>
                        <td><?= $row['note'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="edit('<?= $row['code'] ?>')">编辑</button>
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
    //用戶詳情
    function user_detail(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("user/detail") ?>/' + id
        });
    }
</script>