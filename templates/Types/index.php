<div class="submissions index content">
    <h2><?php echo __('LIST TYPES') ?></h2>

    <div class="submissions-action">
        <?php echo $this->AuthLink->link(__('NEW'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    </div>
    
    <div class="table-responsive">
        <table class="tb">
            <thead>
                <tr>
                    <th class="tb-id"><?php echo $this->Paginator->sort('id', 'ID') ?></th>
                    <th><?php echo $this->Paginator->sort('name') ?></th>
                    <th><?php echo $this->Paginator->sort('url', 'URL') ?></th>
                    <th class="tb-actions"><?php echo __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($types as $type): ?>
                <tr>
                    <td class="tb-id"><?php echo $this->Number->format($type->id) ?></td>
                    <td><?php echo h($type->name) ?></td>
                    <td><?php echo h($type->url) ?></td>
                    <td class="tb-actions">
                        <?php echo $this->AuthLink->link('<i class="fas fa-eye"></i>', ['action' => 'view', $type->id], ['escape' => false]) ?>
                        <?php echo $this->AuthLink->link('<i class="fas fa-highlighter"></i>', ['action' => 'edit', $type->id], ['escape' => false]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="paginator">
        <ul class="pagination">
            <?php
            echo $this->Paginator->first('<<');
            echo $this->Paginator->prev('<');
            echo $this->Paginator->numbers();
            echo $this->Paginator->next('>');
            echo $this->Paginator->last('>>');
            ?>
        </ul>
    </div>
</div>
