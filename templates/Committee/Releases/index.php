<div class="releases index content">
    <?php echo $this->Html->link(__('New Release'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?php echo __('Releases') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?php echo $this->Paginator->sort('id', 'ID') ?></th>
                    <th><?php echo $this->Paginator->sort('acronym') ?></th>
                    <th><?php echo $this->Paginator->sort('release_date') ?></th>
                    <th class="actions"><?php echo __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($releases as $release): ?>
                <tr>
                    <td><?php echo $this->Number->format($release->id) ?></td>
                    <td><?php echo h($release->acronym) ?></td>
                    <td><?php echo h($release->release_date) ?></td>
                    <td class="actions">
                        <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $release->id], ['escape' => false]) ?>
                        <?php echo $this->Html->link('<i class="fas fa-highlighter"></i>', ['action' => 'edit', $release->id], ['escape' => false]) ?>
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
