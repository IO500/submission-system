<?php
use Cake\Core\Configure;
?>

<div class="users form large-10 medium-9 columns">
    <?php echo $this->Form->create($user); ?>

    <fieldset>
        <legend><?php echo __d('cake_d_c/users', 'REGISTRATION') ?></legend>
        <?php
        echo $this->Form->control('username', ['label' => __d('cake_d_c/users', 'Username')]);
        echo $this->Form->control('first_name', ['label' => __d('cake_d_c/users', 'First name')]);
        echo $this->Form->control('last_name', ['label' => __d('cake_d_c/users', 'Last name')]);
        echo $this->Form->control('email', ['label' => __d('cake_d_c/users', 'Email')]);
        echo $this->Form->control('password', ['label' => __d('cake_d_c/users', 'Password')]);
        echo $this->Form->control('password_confirm', [
            'required' => true,
            'type' => 'password',
            'label' => __d('cake_d_c/users', 'Confirm password')
        ]);

        if (Configure::read('Users.Tos.required')) {
            echo $this->Form->control('tos', ['type' => 'checkbox', 'label' => __d('cake_d_c/users', 'Accept TOS conditions?'), 'required' => true]);
        }

        if (Configure::read('Users.reCaptcha.registration')) {
            echo $this->User->addReCaptcha();
        }
        ?>

        <div class="form-buttons">
            <?php
            echo $this->Form->button(__d('cake_d_c/users', 'Submit'));;
            ?>
        </div>
    </fieldset>
    <?php echo $this->Form->end() ?>
</div>
