<ul id="progress-bar">

    <li class="complete">
        <?php
        echo $this->AuthLink->link(__('System Information'),
            [
                'controller' => 'Submissions',
                'action' => 'edit',
                $submission->id
            ]
        );
        ?>
    </li>
    <li class="complete"><?php
        echo $this->AuthLink->link(__('Benchmark Results'),
            [
                'controller' => 'Submissions',
                'action' => 'results',
                $submission->id
            ]
        );
        ?>
    </li>
    <li class="complete"><?php
        echo $this->AuthLink->link(__('Reproducibility Questionnaire'),
            [
                'controller' => 'Questionnaires',
                'action' => 'edit',
                $submission->id
            ]
        );
        ?>
    </li>
    <li class="active">Confirmation</li>
</ul>


<div class="submissions index content">
    <h2><?php echo __('CONFIRMATION') ?></h2>

    <div class="both"></div>
    
    <p>
        Please make sure the information you have provided is complete and correct. Once done, submit it for review by the IO500 Committee.
    </p>

    <?php echo $this->Form->create($submission) ?>

    <p>
        <?php

        echo $this->Form->control('confirmation',
            [
                'type' => 'checkbox',
                'label' => 'I have reviewed my submission',
                'required'
            ]
        );
        ?>
    </p>

    <div class="form-buttons">
        <?php
        echo $this->Form->button(
            __('Submit'),
            [
                'id' => 'submit-site'
            ]
        );
        ?>
    </div>
</div>

<div class="row">
    <div class="column-responsive column-80">
        <div class="submissions view content">
            <h2>Submission #<?php echo h($submission->id) . ' - ' . h($submission->information_system) ?></h2>

            <div class="io-information">
                <div class="information-metadata">
                    <h4>INFORMATION</h4>

                    <table class="tb tb-info">
                        <tr>
                            <th><?php echo _('System') ?></th>
                            <td><?php echo h($submission->information_system) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo _('Institution') ?></th>
                            <td><?php echo h($submission->information_institution) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo _('Storage Vendor') ?></th>
                            <td><?php echo h($submission->information_storage_vendor) ?></td>
                        </tr>
                    </table>
                </div>

                <div class="information-data">
                    <table class="tb tb-info">
                        <tr>
                            <th><?php echo _('Filesystem Type') ?></th>
                            <td><?php echo h($submission->information_filesystem_type) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo _('Filesystem Name') ?></th>
                            <td><?php echo h($submission->information_filesystem_name) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo _('Filesystem Version') ?></th>
                            <td><?php echo h($submission->information_filesystem_version) ?></td>
                        </tr>
                    </table>
                </div>

                <div class="information-metadata">
                    <h4>IO500 SCORES</h4>

                    <table class="tb tb-info">
                        <tr>
                            <th><?php echo _('IO500 Score') ?></th>
                            <td><?php echo $this->Number->format($submission->io500_score, ['places' => 2, 'precision' => 2]) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo _('IO500 BW') ?></th>
                            <td><?php echo $this->Number->format($submission->io500_bw, ['places' => 2, 'precision' => 2]) ?> GiB/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('IO500 MD') ?></th>
                            <td><?php echo $this->Number->format($submission->io500_md, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                    </table>
                </div>

                <div class="information-data">
                    <h4>INFORMATION</h4>

                    <table class="tb tb-info">
                        <tr>
                            <th><?php echo _('Client Nodes') ?></th>
                            <td><?php echo $this->Number->format($submission->information_client_nodes) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo _('Client Total Procs') ?></th>
                            <td><?php echo $this->Number->format($submission->information_client_total_procs) ?></td>
                        </tr>
                        <?php if ($submission->information_md_nodes) { ?>
                        <tr>
                            <th><?php echo _('Metadata Nodes') ?></th>
                            <td><?php echo $this->Number->format($submission->information_md_nodes) ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($submission->information_md_storage_devices) { ?>
                        <tr>
                            <th><?php echo _('Metadata Storage Devices') ?></th>
                            <td><?php echo $this->Number->format($submission->information_md_storage_devices) ?></td>
                        </tr>
                        <?php if ($submission->information_ds_storage_devices) { ?>
                        <?php } ?>
                        <tr>
                            <th><?php echo _('Data Nodes') ?></th>
                            <td><?php echo $this->Number->format($submission->information_ds_nodes) ?></td>
                        </tr>
                        <?php if ($submission->information_ds_storage_devices) { ?>
                        <?php } ?>
                        <tr>
                            <th><?php echo _('Data Storage Devices') ?></th>
                            <td><?php echo $this->Number->format($submission->information_ds_storage_devices) ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>

                <div class="information-metadata">
                    <h4>IOR & FIND</h4>

                    <table class="tb tb-info">
                        <tr>
                            <th><?php echo _('Easy Write') ?></th>
                            <td><?php echo $this->Number->format($submission->ior_easy_write, ['places' => 2, 'precision' => 2]) ?> GiB/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Easy Read') ?></th>
                            <td><?php echo $this->Number->format($submission->ior_easy_read, ['places' => 2, 'precision' => 2]) ?> GiB/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Hard Write') ?></th>
                            <td><?php echo $this->Number->format($submission->ior_hard_write, ['places' => 2, 'precision' => 2]) ?> GiB/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Hard Read') ?></th>
                            <td><?php echo $this->Number->format($submission->ior_hard_read, ['places' => 2, 'precision' => 2]) ?> GiB/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Find') ?></th>
                            <td><?php echo $this->Number->format($submission->find_mixed, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                    </table>
                </div>

                <div class="information-data">
                    <h4>METADATA</h4>

                    <table class="tb tb-info">
                        <tr>
                            <th><?php echo _('Easy Write') ?></th>
                            <td><?php echo $this->Number->format($submission->mdtest_easy_write, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Easy Stat') ?></th>
                            <td><?php echo $this->Number->format($submission->mdtest_easy_stat, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Easy Delete') ?></th>
                            <td><?php echo $this->Number->format($submission->mdtest_easy_delete, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Hard Write') ?></th>
                            <td><?php echo $this->Number->format($submission->mdtest_hard_write, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Hard Read') ?></th>
                            <td><?php echo $this->Number->format($submission->mdtest_hard_read, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Hard Stat') ?></th>
                            <td><?php echo $this->Number->format($submission->mdtest_hard_stat, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                        <tr>
                            <th><?php echo _('Hard Delete') ?></th>
                            <td><?php echo $this->Number->format($submission->mdtest_hard_delete, ['places' => 2, 'precision' => 2]) ?> kIOP/s</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>