<div class="users">
    <h2>
        <?php echo
        $this->Html->tag(
            'span',
            __d('cake_d_c/users', '{0} {1}', $user->first_name, $user->last_name),
            ['class' => 'full_name']
        )
        ?>
    </h2>

    <?php echo $this->Html->link(__d('cake_d_c/users', 'Change Password'), ['plugin' => 'CakeDC/Users', 'controller' => 'Users', 'action' => 'changePassword']); ?>
    <div class="row">
        <div class="large-6 columns strings">
            <h4 class="subheader"><?php echo __d('cake_d_c/users', 'Username') ?></h4>
            <p><?php echo h($user->username) ?></p>
            <h4 class="subheader"><?php echo __d('cake_d_c/users', 'Email') ?></h4>
            <p><?php echo h($user->email) ?></p>
            <?php echo $this->User->socialConnectLinkList($user->social_accounts) ?>
            <?php
            if (!empty($user->social_accounts)):
                ?>
                <h6 class="subheader"><?php echo __d('cake_d_c/users', 'Social Accounts') ?></h6>
                <table cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo __d('cake_d_c/users', 'Avatar'); ?></th>
                        <th><?php echo __d('cake_d_c/users', 'Provider'); ?></th>
                        <th><?php echo __d('cake_d_c/users', 'Link'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($user->social_accounts as $socialAccount):
                        $escapedUsername = h($socialAccount->username);
                        $linkText = empty($escapedUsername) ? __d('cake_d_c/users', 'Link to {0}', h($socialAccount->provider)) : h($socialAccount->username)
                        ?>
                        <tr>
                            <td><?php echo
                                $this->Html->image(
                                    $socialAccount->avatar,
                                    ['width' => '90', 'height' => '90']
                                ) ?>
                            </td>
                            <td><?php echo h($socialAccount->provider) ?></td>
                            <td><?php echo
                                $socialAccount->link && $socialAccount->link != '#' ? $this->Html->link(
                                    $linkText,
                                    $socialAccount->link,
                                    ['target' => '_blank']
                                ) : '-' ?></td>
                        </tr>
                        <?php
                    endforeach;
                    ?>
                    </tbody>
                </table>
                <?php
            endif;
            ?>
        </div>
    </div>
</div>
