<div class="submissions add content">
    <div class="column-responsive column">
        <div class="status form content">
            <fieldset>
                <legend>NEW STATUS</legend>

                <?php
                echo $this->Form->create($status);

                echo $this->Form->control('name');
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