<div class="submissions edit content">
    <div class="column-responsive column">
        <div class="types form content">
            <fieldset>
                <legend>EDIT USER</legend>

                <?php
                echo $this->Form->create($Users);

                echo $this->Form->control('username', ['label' => __d('cake_d_c/users', 'Username')]);
                echo $this->Form->control('email', ['label' => __d('cake_d_c/users', 'Email')]);
                echo $this->Form->control('first_name', ['label' => __d('cake_d_c/users', 'First name')]);
                echo $this->Form->control('last_name', ['label' => __d('cake_d_c/users', 'Last name')]);
                echo $this->Form->control('active', [
                    'label' => __d('cake_d_c/users', 'Active')
                ]);
                ?>

                <div class="form-buttons">
                    <?php
                    echo $this->Form->button(__('SUBMIT'));
                    ?>
                </div>

                <?php
                echo $this->Form->end();
                ?>
            </fieldset>
        </div>
    </div>
</div>