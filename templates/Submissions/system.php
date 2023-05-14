<div class="submissions index content">
    <h2>SUBMISSION #<?php echo h($submission->id) ?></h2>
    
    <div class="both"></div>

    <h3><?php echo __('SYSTEM INFORMATION') ?></h3>
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

            echo $this->Form->button(
                __('Submit'),
                [
                    'id' => 'submit-site',
                    'class' => 'hidden'
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

$url_site = $this->Url->build([
    'controller' => 'webroot',
    'action' => 'files',
    'submissions',
    $submission->id . '.json'
]);


$url_schema = $this->Url->build([
    'controller' => 'webroot',
    'action' => 'model',
    'schema-io500.json'
]);

$this->Html->scriptBlock(
    "
    $(document).ready(function() {
        var spinner = $('#loader');

        dcl_draw_graph = false;
        dcl_draw_table = false;
        dcl_draw_toolbar = false;
        dcl_draw_aggregation = false;
        dcl_global_readonly = true;

        dcl_schema = '" . $url_schema . "';
        dcl_site =  '" . $url_site . "';

        dcl_startup();

        $('#submit-site').click(function(event) {
            event.preventDefault();

            return false;
        });
    });
    ",
    [
        'block' => 'scriptBottom'
    ]
);
?>

<div id="loader"></div>