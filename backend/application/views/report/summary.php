<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?= lists_message() ?>
<div class="box">
    <div class="box-header">
        <form method="post" action="">
            <div class="col-xs-1" style="width:auto;">
                <label>时间</label>
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
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="40%">收入账目</th>
                    <th width="40%">收入金额</th>
                    <th>详情</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($income as $row) : ?>
                    <tr>
                        <td><?= $row['type'] ?></td>
                        <td><?= $row['money'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/refund", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="detail('<?= $row['detail_url'] ?>')">详情</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <table class="table table-hover" style="margin-top:5px;">
            <thead>
                <tr>
                    <th width="40%">支出账目</th>
                    <th width="40%">支出金额</th>
                    <th>详情</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenditure as $row) : ?>
                    <tr>
                        <td><?= $row['type'] ?></td>
                        <td><?= $row['money'] ?></td>
                        <td>
                            <?php if ($this->session->userdata('roleid') == 1 || in_array("{$this->router->class}/refund", $this->allow_url)) : ?>
                                <button type="button" class="btn btn-primary" onclick="detail('<?= $row['detail_url'] ?>')">详情</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4 class="box-body">项目入款(线上充值+线下充值+人工存款) : <?=$item?></h4>
        <h4 class="box-body">虚拟存入(反水+人工彩金+充值彩金) : <?=$virtual?></h4>
        <h4 class="box-body">
            实际收入(线上充值+线下充值+人工存款-会员出款) : 
            <span style="color:<?=base_model::getProfitColor($real)?>"><?=$real?></span>
        </h4>
    </div>
</div>
<script>
    //修改餘額
    function detail(url) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: url
        });
    }
</script>