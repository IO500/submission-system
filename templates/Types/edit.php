<div class="submissions edit content">
    <div class="column-responsive column">
        <div class="types form content">
            <fieldset>
                <legend>EDIT TYPE OF LIST</legend>

                <?php
                echo $this->Form->create($type);

                echo $this->Form->control('name');
                echo $this->Form->control('url');
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
