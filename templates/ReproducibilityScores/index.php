<div class="submissions index content">
    <h2><?php echo __('REPRODUCIBILITY SCORES') ?></h2>

    <div class="submissions-action">
        <?php echo $this->AuthLink->link(__('NEW'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    </div>
    
    <div class="table-responsive">
        <table class="tb">
            <thead>
                <tr>
                    <th class="tb-id"><?php echo $this->Paginator->sort('id') ?></th>
                    <th><?php echo $this->Paginator->sort('name') ?></th>
                    <th class="tb-actions"><?php echo __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reproducibilityScores as $reproducibilityScore) { ?>
                <tr>
                    <td class="tb-id"><?php echo $this->Number->format($reproducibilityScore->id) ?></td>
                    <td><?php echo h($reproducibilityScore->name) ?></td>
                    <td class="tb-actions">
                        <?php echo $this->AuthLink->link('<i class="fas fa-highlighter"></i>', ['action' => 'edit', $reproducibilityScore->id], ['escape' => false]) ?>
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