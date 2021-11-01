<div class="submissions index content">
    <?php echo $this->Html->link(__('New Submission'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?php echo __('Submissions') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?php echo $this->Paginator->sort('id') ?></th>
                    <th><?php echo $this->Paginator->sort('release_id') ?></th>
                    <th><?php echo $this->Paginator->sort('information_system') ?></th>
                    <th><?php echo $this->Paginator->sort('information_institution') ?></th>
                    <th><?php echo $this->Paginator->sort('information_filesystem_type') ?></th>
                    <th><?php echo $this->Paginator->sort('io500_score') ?></th>
                    <th><?php echo $this->Paginator->sort('information_10_node_challenge', 'IO500 List?') ?></th>
                    <th><?php echo $this->Paginator->sort('include_in_io500', '10-NODE List?') ?></th>
                    <th><?php echo $this->Paginator->sort('valid_from') ?></th>
                    <th><?php echo $this->Paginator->sort('valid_to') ?></th>
                    <th class="actions"><?php echo __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                <tr>
                    <td><?php echo $this->Number->format($submission->id) ?></td>
                    <td><?php echo $submission->has('release') ? $this->Html->link($submission->release->acronym, ['controller' => 'Releases', 'action' => 'view', $submission->release->id]) : '' ?></td>
                    <td><?php echo h($submission->information_system) ?></td>
                    <td><?php echo h($submission->information_institution) ?></td>
                    <td><?php echo h($submission->information_filesystem_type) ?></td>
                    <td><?php echo $this->Number->format($submission->io500_score) ?></td>
                    <td>
                        <?php if ($submission->information_10_node_challenge) { ?>
                            <i class="fas fa-check"></i>
                        <?php } else { ?>
                            <i class="fas fa-ban"></i>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($submission->include_in_io500) { ?>
                            <i class="fas fa-check"></i>
                        <?php } else { ?>
                            <i class="fas fa-ban"></i>
                        <?php } ?>
                    </td>
                    <td><?php echo h($submission->valid_from) ?></td>
                    <td><?php echo h($submission->valid_to) ?></td>
                    <td class="actions">
                        <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $submission->id], ['escape' => false]) ?>
                        <?php echo $this->Html->link('<i class="fas fa-highlighter"></i>', ['action' => 'edit', $submission->id], ['escape' => false]) ?>
                        <?php echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $submission->id], ['confirm' => __('Are you sure you want to delete # {0}?', $submission->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?php echo $this->Paginator->first('<< ' . __('first')) ?>
            <?php echo $this->Paginator->prev('< ' . __('previous')) ?>
            <?php echo $this->Paginator->numbers() ?>
            <?php echo $this->Paginator->next(__('next') . ' >') ?>
            <?php echo $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
