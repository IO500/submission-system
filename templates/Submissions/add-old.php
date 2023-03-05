<ul id="progress-bar">
    <li class="active">System Information</li>
    <li class="">Benchmark Results</li>
    <li class="">Reproducibility Questionnaire</li>
    <li class="">Confirmation</li>
</ul>

<div class="submissions index content">
    <h2><?php echo __('NEW SUBMISSION') ?></h2>

    <div class="both"></div>
    
    <p>
        
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
                    'label' => 'Results File (.tar.gz)',
                    'required' => true
                ]);

                echo $this->Form->control('job_script', [
                    'type' => 'file',
                    'required' => false
                ]);

                echo $this->Form->control('job_output', [
                    'type' => 'file',
                    'required' => false
                ]);
                ?>

                <div class="hidden">
                    <?php 
                    echo $this->Form->control('site_json', [
                        'id' => 'site-json',
                        'type' => 'text',
                        'required' => true,
                        'readonly',
                        'class' => 'hidden'
                    ]);

                    //echo $this->Form->control('system_information', [
                    //    'type' => 'file',
                    //    'label' => 'System Information File (.json)',
                    //    'required' => true
                    //]);
                    ?>
                </div>
            </fieldset>
        </div>

        <fieldset>
            <legend>System Information</legend>
    
            <div id="dcl_wrap"></div>
        </fieldset>

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

<?php
echo $this->Html->css([
    'dcl.min.css'
]);

echo $this->Html->script(
    [
        'js-yaml.min.js',
        'c3.min.js',
        'd3.min.js',
        'jquery.min.js',
        'math.min.js',
        'dcl.js',
        'dcl-load.js',
        'dcl-move.js',
        'dcl-vis.js'
    ],
    [
        'block' => 'scriptBottom'
    ]
);

$this->Html->scriptBlock(
    "
    $(document).ready(function() {
        dcl_draw_graph = false;
        dcl_draw_table = false;
        dcl_draw_toolbar = false;
        dcl_draw_aggregation = false;
        dcl_global_readonly = false;

        dcl_schema = 'schema-io500.json';
        dcl_site =  'site-io500.json';

        dcl_startup();

        $('#submit-site').click(function(event) {
            event.preventDefault();

            if (submitDCLChanges()) {
                $('form').submit();
            }
        });
    });
    ",
    [
        'block' => 'scriptBottom'
    ]
);
?>