<h2>商品を出品する</h2>
<?= $this->Form->create($biditem, ['type' => 'file']) ?>
<fieldset>
    <legend>※商品名と終了時間を入力</legend>
    <?php
        echo $this->Form->hidden('user_id', ['value' => $authuser['id']]);
        echo '<p><strong>USER: ' . $authuser['username'] . '</strong></p>';
        echo $this->Form->control('name');
        echo $this->Form->control('description');
        echo '画像をアップロード';
        echo $this->Form->file('image', ['accept' => 'image/*']);
        // echo $this->Form->input('image', array('label' => '画像のアップロード', 'type' => 'file', 'multiple'));
        echo $this->Form->hidden('finished', ['value' => 0]);
        echo $this->Form->control('endtime');
    ?>
</fieldset>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>