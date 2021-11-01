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

                <div class="form-buttons">
                    <?php echo $this->Form->button(__('Submit')) ?>
                </div>
            </fieldset>
        </div>
    </div>
</div>