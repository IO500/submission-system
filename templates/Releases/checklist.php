<div class="submissions index content">
    <h2><?php echo __('CHECKLIST') ?> &mdash; <?php echo h($release->acronym) ?></h2>

    <div class="submissions-action">
        <?php
        echo $this->AuthLink->link(__('EDIT ITEMS'), ['action' => 'editChecklistItems', $release->id], ['class' => 'button float-right']);
        echo $this->Html->link(__('BACK'), ['action' => 'index'], ['class' => 'button float-right']);
        ?>
    </div>

    <?php if (empty($checklist)) { ?>
        <p><?php echo __('No checklist items yet. Use "Edit Items" to add some.') ?></p>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="tb">
                <thead>
                    <tr>
                        <th class="tb-id"><?php echo __('Done') ?></th>
                        <th><?php echo __('Item') ?></th>
                        <th><?php echo __('Status') ?></th>
                        <th><?php echo __('Last user') ?></th>
                        <th><?php echo __('Last updated') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checklist as $item) {
                        $isDone = ($item['status'] ?? 'pending') === 'done';
                        $icon = $isDone ? 'fa-square-check' : 'fa-square';
                        $changedAt = !empty($item['changed_at']) ? new \Cake\I18n\FrozenTime($item['changed_at']) : null;
                        $changedBy = !empty($item['changed_by_id']) ? ($userNames[(string)$item['changed_by_id']] ?? __('Unknown user')) : null;
                    ?>
                    <tr>
                        <td class="tb-id">
                            <?php echo $this->AuthLink->postLink(
                                '<i class="fa-regular ' . $icon . '"></i>',
                                ['action' => 'toggleChecklistItem', $release->id],
                                [
                                    'data' => ['item_key' => $item['key']],
                                    'escape' => false,
                                ]
                            ) ?>
                        </td>
                        <td<?php echo $isDone ? ' style="text-decoration: line-through; opacity: 0.6;"' : '' ?>>
                            <?php echo h($item['label']) ?>
                        </td>
                        <td>
                            <strong class="status status-<?php echo $isDone ? 'done' : 'pending' ?>"><?php echo $isDone ? __('Done') : __('Pending') ?></strong>
                        </td>
                        <td>
                            <?php echo $changedBy ? h($changedBy) : '&mdash;'; ?>
                        </td>
                        <td>
                            <?php if ($changedAt) {
                                echo h($changedAt->nice());
                            } else {
                                echo __('Never');
                            } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
