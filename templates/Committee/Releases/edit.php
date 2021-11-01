<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?php echo __('Actions') ?></h4>
            <?php echo $this->Html->link(__('List Releases'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="releases form content">
            <?php echo $this->Form->create($release) ?>
            <fieldset>
                <legend><?php echo __('Edit Release') ?></legend>
                <?php
                    echo $this->Form->control('acronym');
                    echo $this->Form->control('release_date');
                ?>
            </fieldset>
            <?php echo $this->Form->button(__('Submit')) ?>
            <?php echo $this->Form->end() ?>
        </div>
    </div>
</div>