<div class="users form">
    <?php
    echo $this->Flash->render('auth');
    echo $this->Form->create($user);
    ?>
    
    <fieldset>
        <legend><?php echo __d('cake_d_c/users', 'Please enter the new password') ?></legend>
        
        <?php 
        if ($validatePassword) {
            echo $this->Form->control('current_password', [
                'type' => 'password',
                'required' => true,
                'label' => __d('cake_d_c/users', 'Current password')]
            );
        }

        echo $this->Form->control('password', [
            'type' => 'password',
            'required' => true,
            'label' => __d('cake_d_c/users', 'New password')]
        );
        
        echo $this->Form->control('password_confirm', [
            'type' => 'password',
            'required' => true,
            'label' => __d('cake_d_c/users', 'Confirm password')]
        );
        ?>

        <div class="form-buttons">
            <?php
            echo $this->Form->button(__d('cake_d_c/users', 'Submit'));
            ?>
        </div>
    </fieldset>
    
    <?php echo $this->Form->end() ?>
</div>