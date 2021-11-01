<div class="container">
    <div class="row">
        <div class="col-xs-offset-1 col-xs-10 col-sm-offset-2 col-sm-8 col-md-6 col-md-offset-3">
            <div class="users form well well-lg">
                <?php echo $this->Form->create() ?>

                <?php echo $this->Flash->render('auth') ?>
                <?php echo $this->Flash->render() ?>
                <fieldset>
                    <legend><?php echo __d('cake_d_c/users', 'TWO-FACTOR AUTHENTICATION') ?></legend>

                    <?php if (!empty($secretDataUri)) { ?>
                        <p class='center'><img src="<?php echo $secretDataUri; ?>"/></p>
                    <?php } ?>
                    <?php echo $this->Form->control('code', ['required' => true, 'label' => __d('cake_d_c/users', 'Verification Code')]) ?>

                    <div class="form-buttons">
                        <?php echo $this->Form->button(__d('cake_d_c/users', '<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Verify'), ['class' => 'btn btn-primary', 'escapeTitle' => false]); ?>
                    </div>
                </fieldset>
                <?php echo $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
