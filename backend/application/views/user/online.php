<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>营运商</label>
                <select name="operator_id" class="form-control">
                        <option value="">全部</option>
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['operator_id']) && $where['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>来源</label>
                <select id="platform" name="platform" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (user_model::$platformList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['platform']) && $where['platform'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户类型</label>
                <select id="type" name="type" class="form-control">
                    <?php foreach (user_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '0' ?>><?= $val ?></option>
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
                    <th><?= sort_title('operator_id', '营运商', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('user_name', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('real_name', '姓名', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('agent_id', '代理名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('mobile', '手机号码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('platform', '来源', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('last_login_ip', '登陆IP', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('last_login_time', '登陆时间', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('last_active_time', '最后活跃时间', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '注册日期', $this->cur_url, $order, $where) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['operator_id'] == 0 ? '超级用户':$operator[$row['operator_id']] ?></td>
                        <td><?= $row['type'] == 1 ? '<font color="#aaaaaa">' . $row['user_name'] . '</font>' : $row['user_name'] ?></td>
                        <td><?= $row['real_name'] ?></td>
                        <td><?= $row['agent_name'] ?></td>
                        <td><?= $row['mobile'] ?></td>
                        <td><?= user_model::$platformList[$row['platform']] ?></td>
                        <td><?= $row['last_login_ip'] ?></td>
                        <td><?= $row['last_login_time'] ?></td>
                        <td><?= $row['last_active_time'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/kick", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="kick(<?= $row['id'] ?>)">在线飞踢</button>
                            <?php endif; ?>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/mark", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="mark(<?= $row['id'] ?>)">标记</button>
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
    //在線飛踢
    function kick(id) {
        $.post('<?= site_url("user/kick") ?>',{
            id:id
        },function(data){
            if (data == 'done') {
                alert('飞踢成功!');
                location.reload();
            } else {
                alert('操作失败!');
            }
        });
    }
    //標記
    function mark(id) {
        $.post('<?= site_url("user/mark") ?>',{
            id:id
        },function(data){
            if (data == 'done') {
                alert('标记成功!');
                location.reload();
            } else {
                alert('操作失败!');
            }
        });
    }
</script>