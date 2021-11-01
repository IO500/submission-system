<?php echo $this->Form->create($submission, ['type' => 'file']) ?>

<div class="row">
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Upload New Files</legend>

                <?php
                echo $this->Form->control('result_tar', [
                    'type' => 'file',
                    'label' => 'Results File (.tar.gz)'
                ]);
                echo $submission->result_tar;

                echo $this->Form->control('job_script', [
                    'type' => 'file'
                ]);
                echo $submission->job_script;

                echo $this->Form->control('job_output', [
                    'type' => 'file'
                ]);
                echo $submission->job_output;
                
                echo $this->Form->control('system_information', [
                    'type' => 'file',
                    'label' => 'System Information File (.json)'
                ]);
                ?>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Submission Details</legend>

                <?php
                echo $submission->system_information;

                echo $this->Form->control('information_system', [
                    'label' => 'System'
                ]);
                echo $this->Form->control('information_institution', [
                    'label' => 'Institution'
                ]);
                ?>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_submitter', [
                            'label' => 'Submitter',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_submission_date', [
                            'label' => 'Submission Date',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_storage_vendor', [
                            'label' => 'Storage Vendor'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_filesystem_type', [
                            'label' => 'Filesystem Type'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_client_nodes', [
                            'label' => 'Client Nodes',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_client_total_procs', [
                            'label' => 'Client Total Processes',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>IO500</legend>
                
                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('io500_bw', [
                            'label' => 'Bandwidth',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('io500_md', [
                            'label' => 'Metadata',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('io500_tot_iops', [
                            'label' => 'Total Operations',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('io500_score', [
                            'label' => 'Score',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Storage Information</legend>
                
                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_storage_install_date', [
                            'label' => 'Installation Date',
                            'type' => 'date'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_storage_refresh_date', [
                            'label' => 'Refresh Date',
                            'type' => 'date'
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Filesystem Information</legend>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_filesystem_name', [
                            'label' => 'Name'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_filesystem_version', [
                            'label' => 'Version'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_client_procs_per_node', [
                            'label' => 'Processes Per Client Node'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_client_kernel_version', [
                            'label' => 'Kernel Version'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_client_operating_system', [
                            'label' => 'Operating System'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_client_operating_system_version', [
                            'label' => 'Operating System Version'
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Metadata Server</legend>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_md_nodes', [
                            'label' => 'Nodes'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_md_storage_devices', [
                            'label' => 'Storage Devices'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_md_storage_type', [
                            'label' => 'Storage Type'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_md_storage_interface', [
                            'label' => 'Storage Interface'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_md_volatile_memory_capacity', [
                            'label' => 'Volatile Memory Capacity'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_md_network', [
                            'label' => 'Network'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_md_software_version', [
                            'label' => 'Software Version'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_md_software_version', [
                            'label' => 'Operating System Version'
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Data Server</legend>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_ds_nodes', [
                            'label' => 'Nodes'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_ds_storage_devices', [
                            'label' => 'Storage Devices'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_ds_storage_type', [
                            'label' => 'Storage Type'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_ds_storage_interface', [
                            'label' => 'Storage Interface'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_ds_volatile_memory_capacity', [
                            'label' => 'Volatile Memory Capacity'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_ds_network', [
                            'label' => 'Network'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_ds_software_version', [
                            'label' => 'Software Version'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('information_ds_software_version', [
                            'label' => 'Operating System Version'
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Results from IOR</legend>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('ior_easy_write', [
                            'label' => 'Easy Write',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('ior_easy_read', [
                            'label' => 'Easy Read',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('ior_hard_write', [
                            'label' => 'Hard Write',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('ior_hard_read', [
                            'label' => 'Hard Read',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Results from mdtest</legend>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('mdtest_easy_write', [
                            'label' => 'Easy Write',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('mdtest_easy_stat', [
                            'label' => 'Easy Stat',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('mdtest_easy_delete', [
                            'label' => 'Easy Delete',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('mdtest_hard_write', [
                            'label' => 'Hard Write',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('mdtest_hard_read', [
                            'label' => 'Hard Read',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('mdtest_hard_stat', [
                            'label' => 'Hard Stat',
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        <?php
                        echo $this->Form->control('mdtest_hard_delete', [
                            'label' => 'Hard Delete',
                            'readonly'
                        ]);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Results from find</legend>

                <div class="row">
                    <div class="column">
                        <?php
                        echo $this->Form->control('find_mixed', [
                            'readonly'
                        ]);
                        ?>
                    </div>
                    <div class="column">
                        
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<div class="row">
    <aside class="column">
        
    </aside>
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Review</legend>

                <?php
                echo $this->Form->control('include_in_io500', [
                    'label' => 'Yes, include this submission in the IO500 list!'
                ]);

                echo $this->Form->control('information_10_node_challenge', [
                    'label' => 'Yes, include this submission in the 10-node list!'
                ]);
                ?>

                <div class="form-buttons">
                    <?php echo $this->Form->button(__('Submit')) ?>
                </div>
            </fieldset>
        </div>
    </div>
</div>

<?php echo $this->Form->end() ?>
