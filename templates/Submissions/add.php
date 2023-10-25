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
        Until the next release of the list, the submission committee will handle all submitted data confidentially. That means that we will not disclose any submitted data to individuals/companies, or institutions. By submitting the information you give us the right to publish the uploaded data.
    </p>

    <p>
        <i class="fa-solid fa-circle-exclamation red-stripe"></i> All input fields starting with a <strong class="red-stripe">red stripe</strong> are <strong>mandatory</strong>.
    </p>
</div>

<?php echo $this->Form->create($submission) ?>

<div class="row">
    <div class="column-responsive column-80">
        <fieldset>
            <legend>System Information</legend>
    
            <div id="dcl_wrap"></div>
        </fieldset>

        <div class="form-buttons">
            <?php
            echo $this->Form->control('json',
                [
                    'label' => false
                ]
            );
            ?>

            <p class="notice">
                <i class="fa-solid fa-circle-exclamation"></i> JSON files created before the new system adoption might not be fully compatible and may require you to manually input some fields.
            </p>

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
        'https://unpkg.com/@popperjs/core@2',
        'https://unpkg.com/tippy.js@6',
        'dcl.js',
        'dcl-load.js',
        'dcl-move.js',
        'dcl-vis.js'
    ],
    [
        'block' => 'scriptBottom'
    ]
);

if ($submission->upload_hash) {
    $url_site = $this->Url->build([
        'controller' => 'webroot',
        'action' => 'files',
        'tmp',
        $submission->upload_hash . '.json',
        '?' => [
            'timestamp' => time()
        ]
    ]);
} else {
    $url_site = $this->Url->build([
        'controller' => 'webroot',
        'action' => 'model',
        'site-io500.json'
    ]);
}

$url_schema = $this->Url->build([
    'controller' => 'webroot',
    'action' => 'model',
    'schema-io500.json'
]);

$this->Html->scriptBlock(
    "
    $(document).ready(function() {
        dcl_draw_graph = false;
        dcl_draw_table = false;
        dcl_draw_toolbar = false;
        dcl_draw_aggregation = false;
        dcl_global_readonly = false;

        dcl_schema = '" . $url_schema . "';
        dcl_site =  '" . $url_site . "';

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

<!--
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
-->
