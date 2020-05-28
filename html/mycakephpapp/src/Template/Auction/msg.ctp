<?php if (!empty($bidinfo)): ?>
<h2>商品「<?=$bidinfo->biditem->name ?>」</h2>
<h3>※メッセージ情報</h3>
<h6>※メッセージを送信する</h6>
<?= $this->Form->create($bidmsg) ?>
<?= $this->Form->hidden('bidinfo_id', ['value' => $bidinfo->id]) ?>
<?= $this->Form->hidden('user_id', ['value' => $authuser['id']]) ?>
<?= $this->Form->textarea('message', ['rows' => 2]); ?>
<?= $this->Form->button('Submit') ?>
<?= $this->Form->end() ?>
<table cellpadding="0" cellspacing="0">
<thead>
    <tr>
        <th scope="col">送信者</ht>
        <th class="main" scope="col">メッセージ</th>
        <th scope="col">送信時間</ht>
    </tr>
</thead>
<tbody>
<?php if (!empty($bidmsgs)): ?>
    <?php foreach($bidmsgs as $msg): ?>
    <tr>
        <td><?= h($bsg->user->username) ?></td>
        <td><?= h($bsg->message) ?></td>
        <td><?= h($bsg->created) ?></td>
    </tr>
<?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="3">※メッセージがありません。</td></tr>
<?php endif; ?>
</tbody>
</table>
<?php else: ?>
<h2>※落札情報はありません。</h2>
<?php endif; ?>