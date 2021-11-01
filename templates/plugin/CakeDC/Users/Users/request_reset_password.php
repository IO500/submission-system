<div class="users form">
    <?php echo $this->Flash->render('auth') ?>
    <?php echo $this->Form->create($user) ?>
    <fieldset>
        <legend><?php echo __d('cake_d_c/users', 'PASSWORD RESET') ?></legend>
        <?php echo $this->Form->control('reference', ['label' => 'Please enter your email or username to reset your password']); ?>

        <div class="form-buttons">
            <?php echo $this->Form->button(__d('cake_d_c/users', 'Submit')); ?>
        </div>
    </fieldset>
    <?php echo $this->Form->end() ?>
</div>
