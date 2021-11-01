<div class="submissions index content">
    <h2><?php echo __('RELEASES') ?></h2>

    <div class="submissions-action">
        <?php
        echo $this->AuthLink->link(__('SYNCHRONIZE'), ['action' => 'synchronize'], ['class' => 'button synchronize float-right']);
        echo $this->AuthLink->link(__('NEW'), ['action' => 'add'], ['class' => 'button float-right']);
        ?>
    </div>

    <div class="table-responsive">
        <table class="tb">
            <thead>
                <tr>
                    <th class="tb-id"><?php echo $this->Paginator->sort('id', 'ID') ?></th>
                    <th><?php echo $this->Paginator->sort('acronym') ?></th>
                    <th><?php echo $this->Paginator->sort('release_date') ?></th>
                    <th class="tb-actions"><?php echo __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($releases as $release) { ?>
                <tr>
                    <td class="tb-id"><?php echo $this->Number->format($release->id) ?></td>
                    <td><?php echo h($release->acronym) ?></td>
                    <td><?php echo h($release->release_date) ?></td>
                    <td class="tb-actions">
                        <?php echo $this->AuthLink->link('<i class="fas fa-eye"></i>', ['action' => 'view', $release->id], ['escape' => false]) ?>
                        <?php echo $this->AuthLink->link('<i class="fas fa-highlighter"></i>', ['action' => 'edit', $release->id], ['escape' => false]) ?>
                    </td>
                </tr>
                <?php } ?>
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
