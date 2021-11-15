<div class="row">
    <div class="column-responsive column-80">
        <div class="listings view content">
            <h2><?php echo h($listing->release->acronym) . ' ' . h($listing->type->name) ?> <?php echo $this->Form->postLink('<i class="fas fa-trash icon-right"></i>', ['action' => 'delete', $listing->id], ['confirm' => __('Are you sure you want to delete # {0}?', $listing->id), 'escape' => false]) ?></h2>

            <table class="tb">
                <tr>
                    <th><?php echo __('ID') ?></th>
                    <td><?php echo $this->Number->format($listing->id) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Type') ?></th>
                    <td><?php echo $listing->has('type') ? $this->Html->link($listing->type->name, ['controller' => 'Types', 'action' => 'view', $listing->type->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Release') ?></th>
                    <td><?php echo $listing->has('release') ? $this->Html->link($listing->release->acronym, ['controller' => 'Releases', 'action' => 'view', $listing->release->id]) : '' ?></td>
                </tr>
            </table>

            <div class="text">
                <h2><?php echo __('Description') ?></h2>

                <?php echo $this->Text->autoParagraph($listing->description); ?>
            </div>

            <div class="related">
                <h2><?php echo __('Rank') ?></h2>

                <?php if (!empty($listing->submissions)) { ?>
                <div class="table-responsive">
                    <table class="tb">
                        <tr>
                            <th class="tb-id"><?php echo __('ID') ?></th>
                            <th><?php echo __('Release') ?></th>
                            <th><?php echo __('System') ?></th>
                            <th><?php echo __('Institution') ?></th>
                            <th><?php echo __('Filesystem Type') ?></th>
                            <th><?php echo __('Score') ?></th>
                            <th class="tb-actions"><?php echo __('Actions') ?></th>
                        </tr>
                        <?php foreach ($listing->submissions as $submissions) { ?>
                        <tr>
                            <td class="tb-id"><?php echo h($submissions->id) ?></td>
                            <td><?php echo h($submissions->release->acronym) ?></td>
                            <td><?php echo h($submissions->information_system) ?></td>
                            <td><?php echo h($submissions->information_institution) ?></td>
                            <td><?php echo h($submissions->information_filesystem_type) ?></td>
                            <td><?php echo h($submissions->_joinData->score) ?></td>
                            <td class="tb-actions">
                                <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['controller' => 'Submissions', 'action' => 'view', $submissions->id], ['escape' => false]); ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>