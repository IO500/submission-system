<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Release $release
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Releases'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="releases form content">
            <?= $this->Form->create($release) ?>
            <fieldset>
                <legend><?= __('Add Release') ?></legend>
                <?php
                    echo $this->Form->control('acronym');
                    echo $this->Form->control('release_date');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
