<div class="users form">
    <?php echo $this->Flash->render('auth') ?>
    <?php echo $this->Form->create($user) ?>
    <fieldset>
        <legend><?php echo __d('cake_d_c/users', 'RESEND VALIDATION EMAIL') ?></legend>
        <?php
        echo $this->Form->control('reference', ['label' => __d('cake_d_c/users', 'Email or username')]);
        ?>

        <div class="form-buttons">
            <?php echo $this->Form->button(__d('cake_d_c/users', 'Submit')); ?>
        </div>
    </fieldset>
    <?php echo $this->Form->end() ?>
</div>