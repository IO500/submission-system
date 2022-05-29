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

                <?php if (!empty($submissions)) { ?>
                <div class="table-responsive">
                    <table class="tb">
                        <thead>
                            <tr>
                                <th rowspan="2" class="tb-id"><?php echo $this->Paginator->sort('score', '#') ?></th>
                                <th rowspan="2"><?php echo __('Release') ?></th>
                                <th rowspan="2"><?php echo __('System') ?></th>
                                <th rowspan="2"><?php echo __('Institution') ?></th>
                                <th rowspan="2"><?php echo __('Filesystem Type') ?></th>
                                <th rowspan="2" class="tb-number"><?php echo $this->Paginator->sort('score', _('Score')) ?></th>
                                <th class="tb-center"><?php echo $this->Paginator->sort('io500_bw', _('BW')) ?></th>
                                <th class="tb-center"><?php echo $this->Paginator->sort('io500_md', _('MD')) ?></th>
                            </tr>
                            <tr>
                                <th class="tb-center">(GiB/s)</th>
                                <th class="tb-center">(kIOP/s)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($submissions as $i => $submission) {
                                $url = $this->Url->build([
                                        'controller' => 'submissions',
                                        'action' => 'view',
                                        $submission->submission->id
                                    ]
                                );
                            ?>
                            <tr>
                                <td class="tb-id">
                                    <?php
                                    echo $this->Html->link(($i + 1), [
                                        'controller' => 'submissions',
                                        'action' => 'view',
                                        $submission->submission->id
                                    ], [
                                        'class' => 'rank'
                                    ]);
                                    ?>
                                </td>
                                <td><?php echo h($submission->submission->release->acronym) ?></td>
                                <td><?php echo h($submission->submission->information_system) ?></td>
                                <td><?php echo h($submission->submission->information_institution) ?></td>
                                <td><?php echo h($submission->submission->information_filesystem_type) ?></td>
                                <td class="tb-number"><?php echo $this->Number->format($submission->score, ['places' => 2, 'precision' => 2]) ?></td>
                                <td class="tb-number"><?php echo $this->Number->format($submission->submission->io500_bw, ['places' => 2, 'precision' => 2]) ?></td>
                                <td class="tb-number"><?php echo $this->Number->format($submission->submission->io500_md, ['places' => 2, 'precision' => 2]) ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
