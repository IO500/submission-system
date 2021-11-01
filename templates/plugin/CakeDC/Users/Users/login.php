<?php
use Cake\Core\Configure;
?>

<div class="users form">
    <?php echo $this->Flash->render('auth') ?>

    <?php echo $this->Form->create() ?>
    <fieldset>
        <legend><?php echo __d('cake_d_c/users', 'Authentication') ?></legend>
        <?php
        echo $this->Form->control('email', ['label' => __d('cake_d_c/users', 'Email'), 'required' => true]);
        echo $this->Form->control('password', ['label' => __d('cake_d_c/users', 'Password'), 'required' => true]);

        if (Configure::read('Users.reCaptcha.login')) {
            echo $this->User->addReCaptcha();
        }

        if (Configure::read('Users.RememberMe.active')) {
            echo $this->Form->control(Configure::read('Users.Key.Data.rememberMe'), [
                'type' => 'checkbox',
                'label' => __d('cake_d_c/users', 'Remember me'),
                'checked' => Configure::read('Users.RememberMe.checked')
            ]);
        }

        $registrationActive = Configure::read('Users.Registration.active');
        ?>

        <div class="form-buttons">
            <?php 
            if ($registrationActive) {
                echo $this->Html->link(__d('cake_d_c/users', 'REGISTER'), ['action' => 'register'], ['class' => 'button']);
            }

            if (Configure::read('Users.Email.required')) {
                echo $this->Html->link(__d('cake_d_c/users', 'RESET PASSWORD'), ['action' => 'requestResetPassword'], ['class' => 'button']);
            }

            // echo implode(' ', $this->User->socialLoginList());

            echo $this->Form->button(__d('cake_d_c/users', 'LOGIN'));
            ?>
        </div>
    </fieldset>
    <?php echo $this->Form->end() ?>
</div>
