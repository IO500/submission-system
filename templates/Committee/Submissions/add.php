<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?php echo __('Actions') ?></h4>
            <?php echo $this->Html->link(__('List Submissions'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <?php echo $this->Form->create($submission) ?>
            <fieldset>
                <legend><?php echo __('Add Submission') ?></legend>
                <?php
                    echo $this->Form->control('release_id', ['options' => $releases, 'empty' => true]);
                    echo $this->Form->control('information_system');
                    echo $this->Form->control('information_institution');
                    echo $this->Form->control('information_storage_vendor');
                    echo $this->Form->control('information_filesystem_type');
                    echo $this->Form->control('information_client_nodes');
                    echo $this->Form->control('information_client_total_procs');
                    echo $this->Form->control('io500_score');
                    echo $this->Form->control('io500_bw');
                    echo $this->Form->control('io500_md');
                    echo $this->Form->control('io500_tot_iops');
                    echo $this->Form->control('information_data');
                    echo $this->Form->control('information_10_node_challenge');
                    echo $this->Form->control('information_list_name');
                    echo $this->Form->control('information_identifier');
                    echo $this->Form->control('information_submitter');
                    echo $this->Form->control('information_submission_date', ['empty' => true]);
                    echo $this->Form->control('information_embargo_end_date');
                    echo $this->Form->control('information_storage_install_date');
                    echo $this->Form->control('information_storage_refresh_date');
                    echo $this->Form->control('information_filesystem_name');
                    echo $this->Form->control('information_filesystem_version');
                    echo $this->Form->control('information_client_procs_per_node');
                    echo $this->Form->control('information_client_operating_system');
                    echo $this->Form->control('information_client_operating_system_version');
                    echo $this->Form->control('information_client_kernel_version');
                    echo $this->Form->control('information_md_nodes');
                    echo $this->Form->control('information_md_storage_devices');
                    echo $this->Form->control('information_md_storage_type');
                    echo $this->Form->control('information_md_volatile_memory_capacity');
                    echo $this->Form->control('information_md_storage_interface');
                    echo $this->Form->control('information_md_network');
                    echo $this->Form->control('information_md_software_version');
                    echo $this->Form->control('information_md_operating_system_version');
                    echo $this->Form->control('information_ds_nodes');
                    echo $this->Form->control('information_ds_storage_devices');
                    echo $this->Form->control('information_ds_storage_type');
                    echo $this->Form->control('information_ds_volatile_memory_capacity');
                    echo $this->Form->control('information_ds_storage_interface');
                    echo $this->Form->control('information_ds_network');
                    echo $this->Form->control('information_ds_software_version');
                    echo $this->Form->control('information_ds_operating_system_version');
                    echo $this->Form->control('information_note');
                    echo $this->Form->control('information_best');
                    echo $this->Form->control('ior_easy_write');
                    echo $this->Form->control('ior_easy_read');
                    echo $this->Form->control('ior_hard_write');
                    echo $this->Form->control('ior_hard_read');
                    echo $this->Form->control('mdtest_easy_write');
                    echo $this->Form->control('mdtest_easy_stat');
                    echo $this->Form->control('mdtest_easy_delete');
                    echo $this->Form->control('mdtest_hard_write');
                    echo $this->Form->control('mdtest_hard_read');
                    echo $this->Form->control('mdtest_hard_stat');
                    echo $this->Form->control('mdtest_hard_delete');
                    echo $this->Form->control('find_easy');
                    echo $this->Form->control('find_hard');
                    echo $this->Form->control('marker_score');
                    echo $this->Form->control('marker_md');
                    echo $this->Form->control('storage_data');
                    echo $this->Form->control('status');
                    echo $this->Form->control('include_in_io500');
                    echo $this->Form->control('valid_from', ['empty' => true]);
                    echo $this->Form->control('valid_to', ['empty' => true]);
                ?>
            </fieldset>
            <?php echo $this->Form->button(__('Submit')) ?>
            <?php echo $this->Form->end() ?>
        </div>
    </div>
</div>
