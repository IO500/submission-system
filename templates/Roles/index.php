<div class="roles index content">
    <?php echo $this->Html->link(__('New Role'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?php echo __('Roles') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?php echo $this->Paginator->sort('id') ?></th>
                    <th><?php echo $this->Paginator->sort('name') ?></th>
                    <th><?php echo $this->Paginator->sort('alias') ?></th>
                    <th><?php echo $this->Paginator->sort('created') ?></th>
                    <th><?php echo $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?php echo __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role) { ?>
                <tr>
                    <td><?php echo $this->Number->format($role->id) ?></td>
                    <td><?php echo h($role->name) ?></td>
                    <td><?php echo h($role->alias) ?></td>
                    <td><?php echo h($role->created) ?></td>
                    <td><?php echo h($role->modified) ?></td>
                    <td class="actions">
                        <?php echo $this->Html->link(__('View'), ['action' => 'view', $role->id]) ?>
                        <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $role->id]) ?>
                        <?php echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $role->id], ['confirm' => __('Are you sure you want to delete # {0}?', $role->id)]) ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="paginator">
        <ul class="pagination">
            <?php echo $this->Paginator->first('<<') ?>
            <?php echo $this->Paginator->prev('<') ?>
            <?php echo $this->Paginator->numbers() ?>
            <?php echo $this->Paginator->next('>') ?>
            <?php echo $this->Paginator->last('>>') ?>
        </ul>
    </div>
</div>
