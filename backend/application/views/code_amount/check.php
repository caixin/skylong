<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
            <div class="col-xs-1" style="width:auto;">
                <label>帐户类型</label>
                <select name="money_type" class="form-control">
                    <?php foreach (user_model::$moneyTypeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['money_type']) && $where['money_type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户名称</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="<?= isset($where['user_name']) ? $where['user_name'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>存款类型</label>
                <select name="status" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (code_amount_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['status']) && $where['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>稽核</label>
                <select name="status" class="form-control">
                    <option value="">请选择</option>
                    <?php foreach (code_amount_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['status']) && $where['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>存款时间</label>
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
                    <th><?= sort_title('uid', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('type', '存款类型', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money', '存款金额', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('description', '描述', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('code_amount_need', '需要打码量', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('code_amount', '有效打码量', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('status', '稽核', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('create_time', '存款时间', $this->cur_url, $order, $where) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><a href="javascript:;" onclick="user_detail(<?= $row['uid'] ?>);" style="<?= $row['user_type'] == 1 ? 'color:#aaaaaa;' : '' ?>"><?= $row['user_name'] ?></a></td>
                        <td><?= code_amount_model::$typeList[$row['type']] ?></td>
                        <td><?= $row['money'] ?></td>
                        <td><?= $row['description'] ?></td>
                        <td><?= $row['code_amount_need'] ?></td>
                        <td><a href="javascript:;" onclick="assign(<?= $row['id'] ?>);"><?= $row['code_amount'] ?></a></td>
                        <td style="color:<?= code_amount_model::$statusColorList[$row['status']] ?>;"><?= code_amount_model::$statusList[$row['status']] ?></td>
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
    //打碼量明細
    function assign(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: '<?= site_url("code_amount/assign/sidebar/0/code_amount_id") ?>/' + id
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