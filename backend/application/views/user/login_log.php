<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>营运商</label>
                <select name="operator_id" class="form-control">
                    <?php foreach ($operator as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['operator_id']) && $where['operator_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:150px;">
                <label>用户名称</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:150px;">
                <label>手机号码</label>
                <input type="text" name="mobile" class="form-control" placeholder="请输入..." value="<?= isset($where['mobile']) ? $where['mobile'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:150px;">
                <label>登录IP</label>
                <input type="text" name="ip" class="form-control" placeholder="请输入..." value="<?= isset($where['ip']) ? $where['ip'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>来源</label>
                <select id="source" name="source" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (user_login_log_model::$sourceList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['source']) && $where['source'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>平台</label>
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
                    <option value="">请选择</option>
                    <?php foreach (user_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:250px;">
                <label>登录时间</label>
                <div class="input-group">
                    <input type="text" id="create_time_from" name="create_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['create_time1']) ? $where['create_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="create_time_to" name="create_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['create_time2']) ? $where['create_time2'] : '' ?>" autocomplete="off">
                </div>
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
                    <th><?= sort_title('user_name', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('agent_id', '代理名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('mobile', '手机号码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('ip', '登陆IP', $this->cur_url, $order, $where) ?></th>
                    <th>IP区域</th>
                    <th><?= sort_title('source', '来源', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('platform', '平台', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('domain_url', '網域', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '登录时间', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);" style="<?= $row['user_type'] == 1 ? 'color:#aaaaaa;' : '' ?>"><?= $row['user_name'] ?></a></td>
                        <td><?= $row['agent_name'] ?></td>
                        <td><?= $row['mobile'] ?></td>
                        <td><?= $row['ip'] ?></td>
                        <td><?= $row['country'] ?></td>
                        <td><?= $row['source'] ?></td>
                        <td><?= user_model::$platformList[$row['platform']] ?></td>
                        <td><?= $row['domain_url'] ?></td>
                        <td><?= $row['create_time'] ?></td>
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