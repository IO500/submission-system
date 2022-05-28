<div class="submissions index content">
    <h2><?php echo __('SUBMISSIONS') ?></h2>

    <div class="table-responsive">
        <table class="tb">
            <thead>
                <tr>
                    <th class="tb-id"><?php echo $this->Paginator->sort('id', 'ID') ?></th>
                    <th><?php echo $this->Paginator->sort('release_id', 'Release') ?></th>
                    <th><?php echo $this->Paginator->sort('information_system', 'System') ?></th>
                    <th><?php echo $this->Paginator->sort('information_institution', 'Institution') ?></th>
                    <th><?php echo $this->Paginator->sort('information_filesystem_type', 'Filesystem Type') ?></th>
                    <th class="tb-center"><?php echo $this->Paginator->sort('information_10_node_challenge', '10-NODE') ?></th>
                    <th class="tb-center"><?php echo $this->Paginator->sort('include_in_io500', 'IO500') ?></th>
                    <th class="tb-actions"><?php echo __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission) { ?>
                <tr>
                    <td class="tb-id"><?php echo $this->Number->format($submission->id) ?></td>
                    <td><?php echo $submission->has('release') ? $submission->release->acronym : '' ?></td>
                    <td><?php echo h($submission->information_system) ?></td>
                    <td><?php echo h($submission->information_institution) ?></td>
                    <td><?php echo h($submission->information_filesystem_type) ?></td>
                    <td class="tb-center">
                        <?php if ($submission->information_10_node_challenge === true) { ?>
                            <i class="fas fa-check"></i>
                        <?php } else { ?>
                            <i class="fas fa-ban"></i>
                        <?php } ?>
                    </td>
                    <td class="tb-center">
                        <?php if ($submission->include_in_io500 === true) { ?>
                            <i class="fas fa-check"></i>
                        <?php } else { ?>
                            <i class="fas fa-ban"></i>
                        <?php } ?>
                    </td>
                    <td class="tb-actions">
                        <?php echo $this->AuthLink->link('<i class="fas fa-eye"></i>', ['action' => 'view', $submission->id], ['escape' => false]) ?>
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
