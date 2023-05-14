<div class="submissions index content">
    <h2><?php echo __('USERS') ?></h2>

    <div class="table-responsive">
        <table class="tb">
            <thead>
                <tr>
                    <th><?php echo $this->Paginator->sort('first_name', __d('cake_d_c/users', 'First name')) ?></th>
                    <th><?php echo $this->Paginator->sort('last_name', __d('cake_d_c/users', 'Last name')) ?></th>
                    <th><?php echo $this->Paginator->sort('username', __d('cake_d_c/users', 'Username')) ?></th>
                    <th><?php echo $this->Paginator->sort('email', __d('cake_d_c/users', 'Email')) ?></th>
                    <th class="tb-actions"><?= __d('cake_d_c/users', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (${$tableAlias} as $user) { ?>
                <tr>
                    <td><?php echo h($user->first_name) ?></td>
                    <td><?php echo h($user->last_name) ?></td>
                    <td><?php echo h($user->username) ?></td>
                    <td><?php echo h($user->email) ?></td>
                    <td class="tb-actions">
                        <?php
                        echo $this->Html->link('<i class="fas fa-highlighter"></i>', ['action' => 'edit', $user->id], ['escape' => false]);
                        ?>
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