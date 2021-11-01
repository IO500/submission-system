<div class="row">
    <div class="column-responsive column-80">
        <div class="releases form content">
            <?php echo $this->Form->create($release); ?>

            <fieldset>
                <legend><?php echo __('New Release'); ?></legend>

                <?php
                echo $this->Form->control('acronym');
                echo $this->Form->control('release_date');
                ?>

                <div class="form-buttons">
                    <?php echo $this->Form->button(__('Submit')); ?>
                </div>
            </fieldset>

            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>
