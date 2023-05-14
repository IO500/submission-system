<div class="submissions add content">
    <div class="column-responsive column">
        <div class="types form content">
            <fieldset>
                <legend><?php echo __('NEW REPRODUCIBILITY SCORE'); ?></legend>

                <?php
                echo $this->Form->create($reproducibilityScore);

                echo $this->Form->control('name');
                echo $this->Form->control('description');
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