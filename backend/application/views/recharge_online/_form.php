<?php defined('BASEPATH') || exit('No direct script access allow_naved'); ?>
<div class="box box-<?= validation_errors() ? 'danger' : 'success' ?>">
	<!-- /.box-header -->
	<div class="box-body">
		<form method="post" role="form" action="">
			<div class="form-group <?= form_error('user_group_ids') ? 'has-error' : '' ?>">
				<label>用户分层</label>
				<select name="user_group_ids[]" class="form-control select2" multiple="multiple">
					<?php foreach ($user_group as $key => $val) : ?>
						<option value="<?= $key ?>" <?= isset($row['user_group_ids']) && in_array($key, $row['user_group_ids']) ? 'selected' : '' ?>><?= $val ?></option>
					<?php endforeach; ?>
				</select>
				<?= form_error('user_group_ids', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('interface') ? 'has-error' : '' ?>">
				<label>接口</label>
				<select id="interface" name="interface" class="form-control">
					<?php foreach (recharge_online_model::$interfaceList as $key => $val) : ?>
						<option value="<?= $key ?>" <?= isset($row['interface']) && $row['interface'] == $key ? 'selected' : '' ?>><?= $val ?></option>
					<?php endforeach; ?>
				</select>
				<?= form_error('interface', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('payment') ? 'has-error' : '' ?>">
				<label>付款类型</label>
				<select id="payment" name="payment" class="form-control">
					<?php foreach (recharge_online_model::$paymentList as $key => $val) : ?>
						<option value="<?= $key ?>" <?= isset($row['payment']) && $row['payment'] == $key ? 'selected' : '' ?>><?= $val ?></option>
					<?php endforeach; ?>
				</select>
				<?= form_error('payment', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('pay_url') ? 'has-error' : '' ?>">
				<label>API网址</label>
				<input type="text" name="pay_url" class="form-control" placeholder="Enter ..." value="<?= isset($row['pay_url']) ? $row['pay_url'] : '' ?>">
				<?= form_error('pay_url', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('m_num') ? 'has-error' : '' ?>">
				<label>商户号</label>
				<input type="text" name="m_num" class="form-control" placeholder="Enter ..." value="<?= isset($row['m_num']) ? $row['m_num'] : '' ?>">
				<?= form_error('m_num', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('secret_key') ? 'has-error' : '' ?>">
				<label>密钥</label>
				<input type="text" name="secret_key" class="form-control" placeholder="Enter ..." value="<?= isset($row['secret_key']) ? $row['secret_key'] : '' ?>">
				<?= form_error('secret_key', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('moneys') ? 'has-error' : '' ?>">
				<label>面额 <span style="color:red;">【多个面额用逗号(,)区隔】</span></label>
				<input type="text" name="moneys" class="form-control" placeholder="Enter ..." value="<?= isset($row['moneys']) ? $row['moneys'] : '' ?>">
				<?= form_error('moneys', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('handsel_percent') ? 'has-error' : '' ?>">
				<label>赠送彩金比例</label>
				<input type="number" name="handsel_percent" class="form-control" placeholder="Enter ..." value="<?= isset($row['handsel_percent']) ? $row['handsel_percent'] : '' ?>" step="0.01">
				<?= form_error('handsel_percent', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('handsel_max') ? 'has-error' : '' ?>">
				<label>赠送彩金上限</label>
				<input type="number" name="handsel_max" class="form-control" placeholder="Enter ..." value="<?= isset($row['handsel_max']) ? $row['handsel_max'] : '' ?>">
				<?= form_error('handsel_max', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('multiple') ? 'has-error' : '' ?>">
				<label>打码量倍数</label>
				<input type="number" name="multiple" class="form-control" placeholder="Enter ..." value="<?= isset($row['multiple']) ? $row['multiple'] : '' ?>">
				<?= form_error('multiple', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('min_money') ? 'has-error' : '' ?>">
				<label>单笔最小限额</label>
				<input type="number" name="min_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['min_money']) ? $row['min_money'] : '' ?>" step="0.01">
				<?= form_error('min_money', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('max_money') ? 'has-error' : '' ?>">
				<label>单笔最大限额</label>
				<input type="number" name="max_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['max_money']) ? $row['max_money'] : '' ?>" step="0.01">
				<?= form_error('max_money', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('day_max_money') ? 'has-error' : '' ?>">
				<label>单日最大限额</label>
				<input type="number" name="day_max_money" class="form-control" placeholder="Enter ..." value="<?= isset($row['day_max_money']) ? $row['day_max_money'] : '' ?>" step="0.01">
				<?= form_error('day_max_money', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('status') ? 'has-error' : '' ?>">
				<label>状态</label>
				<select name="status" class="form-control">
					<?php foreach (recharge_online_model::$statusList as $key => $val) : ?>
						<option value="<?= $key ?>" <?= isset($row['status']) && $row['status'] == $key ? 'selected' : '' ?>><?= $val ?></option>
					<?php endforeach; ?>
				</select>
				<?= form_error('status', '<span class="help-block">', '</span>') ?>
			</div>
			<div class="form-group <?= form_error('sort') ? 'has-error' : '' ?>">
				<label>排序</label>
				<input type="text" name="sort" class="form-control" placeholder="Enter ..." value="<?= isset($row['sort']) ? $row['sort'] : '' ?>">
				<?= form_error('sort', '<span class="help-block">', '</span>') ?>
			</div>
			<button type="submit" class="btn btn-primary">保存</button>
		</form>
	</div>
	<!-- /.box-body -->
</div>
<!-- /.box -->
<script>
	$('.select2').select2();
</script>