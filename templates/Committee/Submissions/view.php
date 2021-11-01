<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?php echo __('Actions') ?></h4>
            <?php echo $this->Html->link(__('List Submissions'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions view content">
            <h3><?php echo h($submission->information_filesystem_type) . ' (' . h($submission->information_system) . ') @ ' . h($submission->information_institution) ?></h3>
            <table>
                <tr>
                    <th><?php echo __('ID') ?></th>
                    <td><?php echo $this->Number->format($submission->id) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Release') ?></th>
                    <td><?php echo $submission->has('release') ? $this->Html->link($submission->release->name, ['controller' => 'Releases', 'action' => 'view', $submission->release->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?php echo __('System') ?></th>
                    <td><?php echo h($submission->information_system) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Institution') ?></th>
                    <td><?php echo h($submission->information_institution) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Storage Vendor') ?></th>
                    <td><?php echo h($submission->information_storage_vendor) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Filesystem Type') ?></th>
                    <td><?php echo h($submission->information_filesystem_type) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data') ?></th>
                    <td><?php echo h($submission->information_data) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Submitter') ?></th>
                    <td><?php echo h($submission->information_submitter) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Embargo End Date') ?></th>
                    <td><?php echo h($submission->information_embargo_end_date) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Storage Install Date') ?></th>
                    <td><?php echo h($submission->information_storage_install_date) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Storage Refresh Date') ?></th>
                    <td><?php echo h($submission->information_storage_refresh_date) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Filesystem Name') ?></th>
                    <td><?php echo h($submission->information_filesystem_name) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Filesystem Version') ?></th>
                    <td><?php echo h($submission->information_filesystem_version) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Client Procs Per Node') ?></th>
                    <td><?php echo h($submission->information_client_procs_per_node) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Client Operating System') ?></th>
                    <td><?php echo h($submission->information_client_operating_system) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Client Operating System Version') ?></th>
                    <td><?php echo h($submission->information_client_operating_system_version) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Client Kernel Version') ?></th>
                    <td><?php echo h($submission->information_client_kernel_version) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Metadata Nodes') ?></th>
                    <td><?php echo h($submission->information_md_nodes) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Metadata Storage Devices') ?></th>
                    <td><?php echo h($submission->information_md_storage_devices) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Metadata Storage Type') ?></th>
                    <td><?php echo h($submission->information_md_storage_type) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Metadata Volatile Memory Capacity') ?></th>
                    <td><?php echo h($submission->information_md_volatile_memory_capacity) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Metadata Storage Interface') ?></th>
                    <td><?php echo h($submission->information_md_storage_interface) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Metadata Network') ?></th>
                    <td><?php echo h($submission->information_md_network) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Metadata Software Version') ?></th>
                    <td><?php echo h($submission->information_md_software_version) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Metadata Operating System Version') ?></th>
                    <td><?php echo h($submission->information_md_operating_system_version) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data Server Nodes') ?></th>
                    <td><?php echo h($submission->information_ds_nodes) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data Server Storage Devices') ?></th>
                    <td><?php echo h($submission->information_ds_storage_devices) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data Server Storage Type') ?></th>
                    <td><?php echo h($submission->information_ds_storage_type) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data Server Volatile Memory Capacity') ?></th>
                    <td><?php echo h($submission->information_ds_volatile_memory_capacity) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data Server Storage Interface') ?></th>
                    <td><?php echo h($submission->information_ds_storage_interface) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data Server Network') ?></th>
                    <td><?php echo h($submission->information_ds_network) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data Server Software Version') ?></th>
                    <td><?php echo h($submission->information_ds_software_version) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Data Server Operating System Version') ?></th>
                    <td><?php echo h($submission->information_ds_operating_system_version) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Note') ?></th>
                    <td><?php echo h($submission->information_note) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Best') ?></th>
                    <td><?php echo h($submission->information_best) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Marker Score') ?></th>
                    <td><?php echo h($submission->marker_score) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Marker Metadata') ?></th>
                    <td><?php echo h($submission->marker_md) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Client Nodes') ?></th>
                    <td><?php echo $this->Number->format($submission->information_client_nodes) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Client Total Procs') ?></th>
                    <td><?php echo $this->Number->format($submission->information_client_total_procs) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('IO500 Score') ?></th>
                    <td><?php echo $this->Number->format($submission->io500_score) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('IO500 Bandwidth') ?></th>
                    <td><?php echo $this->Number->format($submission->io500_bw) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('IO500 Metadata') ?></th>
                    <td><?php echo $this->Number->format($submission->io500_md) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('IO500 Total IOPS') ?></th>
                    <td><?php echo $this->Number->format($submission->io500_tot_iops) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('IOR Easy Write') ?></th>
                    <td><?php echo $this->Number->format($submission->ior_easy_write) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Find Easy') ?></th>
                    <td><?php echo $this->Number->format($submission->find_easy) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Submission Date') ?></th>
                    <td><?php echo h($submission->information_submission_date) ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Information 10 Node Challenge') ?></th>
                    <td><?php echo $submission->information_10_node_challenge ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Include In Io500') ?></th>
                    <td><?php echo $submission->include_in_io500 ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?php echo __('IOR Easy Read') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->ior_easy_read)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('IOR Hard Write') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->ior_hard_write)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('IOR Hard Read') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->ior_hard_read)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Mdtest Easy Write') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->mdtest_easy_write)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Mdtest Easy Stat') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->mdtest_easy_stat)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Mdtest Easy Delete') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->mdtest_easy_delete)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Mdtest Hard Write') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->mdtest_hard_write)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Mdtest Hard Read') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->mdtest_hard_read)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Mdtest Hard Stat') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->mdtest_hard_stat)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Mdtest Hard Delete') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->mdtest_hard_delete)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Find Hard') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->find_hard)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Storage Data') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->storage_data)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?php echo __('Status') ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($submission->status)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
