<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <input type="hidden" name="sidebar" value="<?= isset($where['sidebar']) ? $where['sidebar'] : '' ?>">
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
                <label>邀请码</label>
                <input type="text" name="agent_code" class="form-control" placeholder="请输入..." value="<?= isset($where['agent_code']) ? $where['agent_code'] : '' ?>">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户类型</label>
                <select name="type" class="form-control">
                    <?php foreach (User_model::$typeList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['type']) && $where['type'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户狀態</label>
                <select name="status" class="form-control">
                    <option value="">全部</option>
                    <?php foreach (User_model::$statusList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['status']) && $where['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>所属分层</label>
                <select name="user_group_id" class="form-control">
                    <option value="">全部</option>
                    <?php foreach ($user_group as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['user_group_id']) && $where['user_group_id'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1" style="width:250px;">
                <label>添加日期</label>
                <div class="input-group">
                    <input type="text" id="create_time_from" name="create_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="<?= isset($where['create_time1']) ? $where['create_time1'] : '' ?>" autocomplete="off">
                    <input type="text" id="create_time_to" name="create_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="<?= isset($where['create_time2']) ? $where['create_time2'] : '' ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>VIP包</label>
                <select name="vip_info_ios" class="form-control">
                    <option value="">全部</option>
                    <?php foreach (User_model::$whetherList as $key => $val) : ?>
                        <option value="<?= $key ?>" <?= isset($where['vip_info_ios']) && $where['vip_info_ios'] == $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-1">
                <label>&nbsp;</label>
                <button type="submit" class="form-control btn btn-primary">查询</button>
            </div>
        </form>
    </div>
    <div class="box-header">
        <label for="per_page">显示笔数:</label>
        <input type="test" id="per_page" style="text-align:center;" value="<?= $this->per_page ?>" size="1">
        <h5 class="box-title" style="font-size: 14px;">
            <b>总计:</b> <?= $total ?> &nbsp;
            <b style="color:red;">蓝色为玩家邀请码，红色为代理邀请码</b>
        </h5>
        <?= $this->pagination->create_links() ?>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <form method="post" action="<?=site_url("{$this->router->class}/batch")?>" onsubmit="if (!confirm('您确定要批量设置吗?')) return false;">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><input type="checkbox" id="check_all"></th>
                    <th><?= sort_title('id', '编号', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('user_name', '用户名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('agent_id', '代理名称', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('agent_code', '注册邀请码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('mobile', '手机号码', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('real_name', '姓名', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money', user_model::$moneyTypeList[0], $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('money1', user_model::$moneyTypeList[1], $this->cur_url, $order, $where) ?></th>
                    <th>输赢</th>
                    <th><?= sort_title('type', '用户类型', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('status', '狀態', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('user_group_id', '所属分层', $this->cur_url, $order, $where) ?></th>
                    <th><?= sort_title('last_login_ip', '登录IP', $this->cur_url, $order, $where) ?></th>
                    <th width="100"><?= sort_title('create_time', '注册日期', $this->cur_url, $order, $where) ?></th>
                    <th width="100"><?= sort_title('last_login_time', '最后登录', $this->cur_url, $order, $where) ?></th>
                    <th width="160">
                        <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/create", $this->allow_url)) : ?>
                            <button type="button" class="btn btn-primary" onclick="add()">添加</button>
                        <?php endif; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row) : ?>
                    <tr>
                        <td><input type="checkbox" name="id[]" value="<?=$row['id']?>"></td>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['type'] == 1 ? '<font color="#aaaaaa">' . $row['user_name'] . '</font>' : $row['user_name'] ?></td>
                        <td><?= $row['agent_name'] ?></td>
                        <td style="color:<?= $row['code_color'] ?>"><?= $row['agent_code'] ?></td>
                        <td><?= $row['mobile'] ?></td>
                        <td><?= $row['real_name'] ?></td>
                        <td><?= $row['money'] ?></td>
                        <td><?= $row['money1'] ?></td>
                        <td style="color:<?=base_model::getProfitColor($row['profit'])?>"><?= $row['profit'] ?></td>
                        <td><?= user_model::$typeList[$row['type']] ?></td>
                        <td style="color:<?=user_model::$statusColor[$row['status']]?>"><?= user_model::$statusList[$row['status']] ?></td>
                        <td><?= $user_group[$row['user_group_id']] ?></td>
                        <td><?= $row['last_login_ip'] ?></td>
                        <td><?= $row['create_time'] ?></td>
                        <td><?= $row['last_login_time'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit_pwd", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" style="margin-bottom: 3px;" onclick="edit_pwd(<?= $row['id'] ?>)">重置密码</button>
                            <?php endif; ?>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" style="margin-bottom: 3px;" onclick="edit(<?= $row['id'] ?>)">编辑</button>
                            <?php endif; ?>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/edit_money", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="edit_money(<?= $row['id'] ?>)">人工存款</button>
                            <?php endif; ?>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/remark", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="remark(<?= $row['id'] ?>)">备注</button>
                            <?php endif; ?>
                            <?php if (($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/vip_info_ios", $this->allow_url)) && $row['vip_info_ios'] != '') : ?>
                                <button type="button" class="btn btn-primary" onclick="vip_info_ios(<?= $row['id'] ?>)">ios绑定vip</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" rowspan="2">
                        <input type="hidden" name="params_uri" value="<?=$params_uri?>">
                        <input type="hidden" name="page" value="<?=$page?>">
                        <div class="col-xs-1" style="width:150px;">
                            <select name="user_group_id" class="form-control">
                            <?php foreach ($user_group as $key => $val) : ?>
                                <option value="<?= $key ?>"><?= $val ?></option>
                            <?php endforeach; ?>
                            </select>
                            <input type="submit" name="group_btn" class="form-control btn btn-primary" value="批量设定分层">
                        </div>
                        <div class="col-xs-1" style="width:150px;">
                            <input type="text" name="agent_code" class="form-control" placeholder="代理邀请码">
                            <input type="submit" name="agent_btn" class="form-control btn btn-primary" value="批量设定代理">
                        </div>
                    </td>
                    <td style="color:blue;">当页:</td>
                    <td><?=$page_money?></td>
                    <td><?=$page_money1?></td>
                    <td style="color:<?=base_model::getProfitColor($page_profit)?>"><?=$page_profit?></td>
                    <td colspan="99"></td>
                </tr>
                <tr>
                    <td style="color:blue;">总计:</td>
                    <td><?=$total_money?></td>
                    <td><?=$total_money1?></td>
                    <td style="color:<?=base_model::getProfitColor($total_profit)?>"><?=$total_profit?></td>
                    <td colspan="99"></td>
                </tr>
            </tfoot>
        </table>
        </form>
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
            content: '<?= site_url("{$this->router->class}/create/$where[operator_id]") ?>'
        });
    }
    //编辑
    function edit_pwd(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/edit_pwd") ?>/' + id
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
    //修改餘額
    function edit_money(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/edit_money") ?>/' + id
        });
    }
    //備註
    function remark(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: '<?= site_url("{$this->router->class}/remark") ?>/' + id
        });
    }
    //ios綁定vip
    function vip_info_ios(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '<?= site_url("{$this->router->class}/vip_info_ios") ?>/' + id
        });
    }

    $('#check_all').click(function(){
        $('input[name="id[]"]').each(function(){
            $(this).prop('checked',$('#check_all').prop('checked'));
        });
    });
</script>