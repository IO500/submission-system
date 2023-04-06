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
    <li class="active">Benchmark Results</li>
    <li class="">Reproducibility Questionnaire</li>
    <li class="">Confirmation</li>
</ul>

<div class="submissions index content">
    <h2><?php echo __('BENCHMARK RESULTS') ?></h2>

    <div class="both"></div>
    
    <p>
        Please make sure all files are correctly submitted. You should provide the automatically generated results tarball of the IO500 run; the job script you used to start the benchmark (or manually generated script to execute); and the output (stdout and stderr) of the job execution, typically generated by the workflow management system.
    </p>

    <p>
        <i class="fa-solid fa-circle-exclamation red-stripe"></i> All input fields are <strong>mandatory</strong>.
    </p>
</div>


<?php echo $this->Form->create($submission, ['type' => 'file']) ?>

<div class="row">
    <div class="column-responsive column-80">
        <div class="submissions form content">
            <fieldset>
                <legend>Upload New Files</legend>

                <?php
                echo $this->Form->control('result_tar', [
                    'type' => 'file',
                    'label' => 'Results File (.zip or .tar.gz)',
                    'required' => ($submission->result_tar ? false : true)
                ]);
                ?>

                <?php if ($submission->result_tar && is_file(ROOT . DS . $submission->result_tar_dir . $submission->result_tar)) { ?>
                <span><?php echo $submission->result_tar; ?> (<?php echo $submission->result_tar_size; ?> bytes) <i class="md5">MD5 [<?php echo md5_file(ROOT . DS . $submission->result_tar_dir . $submission->result_tar); ?>]</i></span>
                <?php } ?>

                <?php
                echo $this->Form->control('job_script', [
                    'type' => 'file',
                    'required' => ($submission->job_script ? false : true)
                ]);
                ?>

                <?php if ($submission->job_script && is_file(ROOT . DS . $submission->job_script_dir . $submission->job_script)) { ?>
                <span><?php echo $submission->job_script; ?> (<?php echo $submission->job_script_size; ?> bytes) <i class="md5">MD5 [<?php echo md5_file(ROOT . DS . $submission->job_script_dir . $submission->job_script); ?>]</i></span>
                <?php } ?>

                <?php
                echo $this->Form->control('job_output', [
                    'type' => 'file',
                    'required' => ($submission->job_output ? false : true)
                ]);
                ?>

                <?php if ($submission->job_output && is_file(ROOT . DS . $submission->job_output_dir . $submission->job_output)) { ?>
                <span><?php echo $submission->job_output; ?> (<?php echo $submission->job_output_size; ?> bytes) <i class="md5">MD5 [<?php echo md5_file(ROOT . DS . $submission->job_output_dir . $submission->job_output); ?>]</i></span>
                <?php } ?>
            </fieldset>
        </div>

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
</div>