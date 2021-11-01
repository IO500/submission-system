<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?php echo __('Actions') ?></h4>
            <?php echo $this->Html->link(__('Edit Release'), ['action' => 'edit', $release->id], ['class' => 'side-nav-item']) ?>
            <?php echo $this->Html->link(__('List Releases'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
</div>
<div class="row">
    <div class="column-responsive">
        <div class="releases view content">
            <h3><?php echo h($release->acronym) ?> Release</h3>
            <table>
                <tr>
                    <th><?php echo __('ID') ?></th>
                    <td><?php echo $this->Number->format($release->id) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Acronym') ?></th>
                    <td><?php echo h($release->acronym) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Release Date') ?></th>
                    <td><?php echo h($release->release_date) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?php echo __('Related Submissions') ?></h4>
                <?php if (!empty($release->submissions)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?php echo __('ID') ?></th>
                            <th><?php echo __('System') ?></th>
                            <th><?php echo __('Institution') ?></th>
                            <th><?php echo __('Filesystem Type') ?></th>
                            <th class="actions"><?php echo __('Actions') ?></th>
                        </tr>
                        <?php foreach ($release->submissions as $submissions) : ?>
                        <tr>
                            <td><?php echo h($submissions->id) ?></td>
                            <td><?php echo h($submissions->information_system) ?></td>
                            <td><?php echo h($submissions->information_institution) ?></td>
                            <td><?php echo h($submissions->information_filesystem_type) ?></td>
                            <td class="actions">
                                <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['controller' => 'Submissions', 'action' => 'view', $submissions->id], ['escape' => false]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
