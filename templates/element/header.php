<nav>
    <div class="container navigation">
        <div class="logo">
            <a href="<?php echo $this->Url->build('/') ?>">IO500<span>HUB</span></a> <?php echo strtoupper($this->getRequest()->getSession()->read('Auth.role')); ?> ACCESS
        </div>

        <ul class="links">
            <li>
                <?php
                echo $this->AuthLink->link(__('Users'),
                    [
                        'controller' => 'users',
                        'action' => 'index',
                        'plugin' => 'CakeDC/Users'
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('Types'),
                    [
                        'controller' => 'types',
                        'action' => 'index',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('Releases'),
                    [
                        'controller' => 'releases',
                        'action' => 'index',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('Status'),
                    [
                        'controller' => 'status',
                        'action' => 'index',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('Scores'),
                    [
                        'controller' => 'reproducibilityScores',
                        'action' => 'index',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('My Submissions'),
                    [
                        'controller' => 'submissions',
                        'action' => 'mine',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('New Submission'),
                    [
                        'controller' => 'submissions',
                        'action' => 'add',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('Submissions'),
                    [
                        'controller' => 'submissions',
                        'action' => 'index',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('Users'),
                    [
                        'controller' => 'users',
                        'action' => 'index',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('Account'),
                    [
                        'controller' => 'users',
                        'action' => 'profile',
                        'plugin' => null
                    ]
                );
                ?>
            </li>
            <li>
                <?php
                echo $this->AuthLink->link(__('Logout'),
                    '/logout'
                );
                ?>
            </li>
        </ul>
    </div>
</nav>
