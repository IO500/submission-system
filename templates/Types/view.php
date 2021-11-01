<div class="row">
    <div class="column-responsive column-80">
        <div class="types view content">
            <h2><?php echo h($type->name) ?></h2>
    
            <table class="tb">
                <tr>
                    <th><?php echo __('ID') ?></th>
                    <td><?php echo $this->Number->format($type->id) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Name') ?></th>
                    <td><?php echo h($type->name) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('URL') ?></th>
                    <td><?php echo h($type->url) ?></td>
                </tr>
            </table>

            <div class="related">
                <h2><?php echo __('Related Listings') ?></h2>

                <?php if (!empty($type->listings)) { ?>
                <div class="table-responsive">
                    <table class="tb">
                        <tr>
                            <th class="tb-id"><?php echo __('ID') ?></th>
                            <th><?php echo __('Type') ?></th>
                            <th><?php echo __('Release') ?></th>
                            <th class="tb-actions"><?php echo __('Actions') ?></th>
                        </tr>
                        <?php foreach ($type->listings as $listings) { ?>
                        <tr>
                            <td class="tb-id"><?php echo h($listings->id) ?></td>
                            <td><?php echo h($listings->type->name) ?></td>
                            <td><?php echo h($listings->release->acronym) ?></td>
                            <td class="tb-actions">
                                <?php echo $this->AuthLink->link('<i class="fas fa-eye"></i>', ['controller' => 'Listings', 'action' => 'view', $listings->id], ['escape' => false]) ?>
                                <?php echo $this->AuthLink->link('<i class="fas fa-highlighter"></i>', ['controller' => 'Listings', 'action' => 'edit', $listings->id], ['escape' => false]) ?>
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
