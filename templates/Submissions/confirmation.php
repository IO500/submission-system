<?php
/**
 * Renders a label/value table row on the confirmation page and flags empty ones.
 *
 * Each helper returns a full <tr>. When the field is empty the row gets the
 * "row-empty" class (highlighted) and $emptyFields is incremented so a single
 * banner can be shown before the submit button when anything is missing. For
 * numeric fields a real 0 counts as provided; only null/empty is treated as
 * missing.
 *
 * The review tables are rendered into a buffer first so $emptyFields is known by
 * the time the form (and its banner) is rendered above them.
 */
$emptyFields = 0;

$renderRow = function ($label, $empty, $display) use (&$emptyFields) {
    if ($empty) {
        $emptyFields++;
    }

    return '<tr' . ($empty ? ' class="row-empty"' : '') . '>'
        . '<th>' . h($label) . '</th>'
        . '<td>' . ($empty ? '<i class="fa-solid fa-circle-exclamation row-empty-icon"></i>' : $display) . '</td>'
        . '</tr>';
};

$rowText = function ($label, $v) use ($renderRow) {
    return $renderRow($label, $v === null || $v === '', h($v));
};

$rowScore = function ($label, $v, $unit = '') use ($renderRow) {
    $display = $this->Number->format((float)$v, ['places' => 2, 'precision' => 2]) . ($unit !== '' ? ' ' . $unit : '');

    return $renderRow($label, $v === null || $v === '', $display);
};

$rowCount = function ($label, $v) use ($renderRow) {
    return $renderRow($label, $v === null || $v === '', $this->Number->format((float)$v));
};

ob_start();
?>
<div class="row">
    <div class="column-responsive column-80">
        <div class="submissions view content">
            <h2>Submission #<?php echo h($submission->id) . ' - ' . h($submission->information_system) ?></h2>

            <div class="io-information">
                <div class="information-metadata">
                    <h4>INFORMATION</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowText(_('System'), $submission->information_system);
                        echo $rowText(_('Institution'), $submission->information_institution);
                        echo $rowText(_('Storage Vendor'), $submission->information_storage_vendor);
                        ?>
                    </table>
                </div>

                <div class="information-data">
                    <table class="tb tb-info">
                        <?php
                        echo $rowText(_('Filesystem Type'), $submission->information_filesystem_type);
                        echo $rowText(_('Filesystem Name'), $submission->information_filesystem_name);
                        echo $rowText(_('Filesystem Version'), $submission->information_filesystem_version);
                        ?>
                    </table>
                </div>

                <div class="information-metadata">
                    <h4>METADATA SERVER</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowText(_('Storage Type'), $submission->information_md_storage_type);
                        echo $rowText(_('Volatile Memory'), $submission->information_md_volatile_memory_capacity);
                        echo $rowText(_('Storage Interface'), $submission->information_md_storage_interface);
                        echo $rowText(_('Network'), $submission->information_md_network);
                        echo $rowText(_('Software Version'), $submission->information_md_software_version);
                        echo $rowText(_('OS Version'), $submission->information_md_operating_system_version);
                        ?>
                    </table>
                </div>

                <div class="information-data">
                    <h4>DATA SERVER</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowText(_('Storage Type'), $submission->information_ds_storage_type);
                        echo $rowText(_('Volatile Memory'), $submission->information_ds_volatile_memory_capacity);
                        echo $rowText(_('Storage Interface'), $submission->information_ds_storage_interface);
                        echo $rowText(_('Network'), $submission->information_ds_network);
                        echo $rowText(_('Software Version'), $submission->information_ds_software_version);
                        echo $rowText(_('OS Version'), $submission->information_ds_operating_system_version);
                        ?>
                    </table>
                </div>

                <div class="information-metadata">
                    <h4>IO500 SCORES</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowScore(_('IO500 Score'), $submission->io500_score);
                        echo $rowScore(_('IO500 BW'), $submission->io500_bw, 'GiB/s');
                        echo $rowScore(_('IO500 MD'), $submission->io500_md, 'kIOP/s');
                        ?>
                    </table>
                </div>

                <div class="information-data">
                    <h4>INFORMATION</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowCount(_('Client Nodes'), $submission->information_client_nodes);
                        echo $rowCount(_('Client Total Procs'), $submission->information_client_total_procs);
                        ?>
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
                        <?php } ?>
                        <?php if ($submission->information_ds_nodes) { ?>
                        <tr>
                            <th><?php echo _('Data Nodes') ?></th>
                            <td><?php echo $this->Number->format($submission->information_ds_nodes) ?></td>
                        </tr>
                        <?php } ?>
                        <?php if ($submission->information_ds_storage_devices) { ?>
                        <tr>
                            <th><?php echo _('Data Storage Devices') ?></th>
                            <td><?php echo $this->Number->format($submission->information_ds_storage_devices) ?></td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>

                <div class="information-metadata">
                    <h4>IOR</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowScore(_('Easy Write'), $submission->ior_easy_write, 'GiB/s');
                        echo $rowScore(_('Easy Read'), $submission->ior_easy_read, 'GiB/s');
                        echo $rowScore(_('Hard Write'), $submission->ior_hard_write, 'GiB/s');
                        echo $rowScore(_('Hard Read'), $submission->ior_hard_read, 'GiB/s');
                        ?>
                    </table>
                </div>

                <div class="information-data">
                    <h4>METADATA</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowScore(_('Easy Write'), $submission->mdtest_easy_write, 'kIOP/s');
                        echo $rowScore(_('Easy Stat'), $submission->mdtest_easy_stat, 'kIOP/s');
                        echo $rowScore(_('Easy Delete'), $submission->mdtest_easy_delete, 'kIOP/s');
                        echo $rowScore(_('Hard Write'), $submission->mdtest_hard_write, 'kIOP/s');
                        echo $rowScore(_('Hard Read'), $submission->mdtest_hard_read, 'kIOP/s');
                        echo $rowScore(_('Hard Stat'), $submission->mdtest_hard_stat, 'kIOP/s');
                        echo $rowScore(_('Hard Delete'), $submission->mdtest_hard_delete, 'kIOP/s');
                        ?>
                    </table>
                </div>

                <div class="information-metadata">
                    <h4>RANDOM</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowScore(_('4KiB Reads'), $submission->ior_easy_read_random, 'GiB/s');
                        echo $rowScore('', $submission->ior_easy_read_random === null ? null : $submission->ior_easy_read_random * 256, 'kIOP/s');
                        ?>
                    </table>
                </div>

                <div class="information-metadata">
                    <h4>FIND</h4>

                    <table class="tb tb-info">
                        <?php
                        echo $rowScore(_('Find'), $submission->find_mixed, 'kIOP/s');
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$reviewContent = ob_get_clean();
?>
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

    <p>
        <?php

        echo $this->Form->control('acknowledged_rules',
            [
                'type' => 'checkbox',
                'label' => [
                    'escape' => false,
                    'text' => 'I acknowledge that I have read and followed the '
                        . '<a href="https://io500.org/rules/submission" target="_blank" rel="noopener noreferrer">IO500 submission rules</a>.',
                ],
                'required'
            ]
        );
        ?>
    </p>

    <p>
        <?php

        echo $this->Form->control('acknowledged_publication',
            [
                'type' => 'checkbox',
                'label' => 'I grant IO500 permission to publish and use these results for the official rankings and analysis.',
                'required'
            ]
        );
        ?>
    </p>

    <?php if ($emptyFields > 0) : ?>
    <div class="submission-notice">
        <?php echo __('Some fields in your submission below are empty. Please make sure it is complete and correct before submitting it for review.') ?>
    </div>
    <?php endif; ?>

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

<?php echo $reviewContent; ?>
