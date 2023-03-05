<div id="dcl_wrap"></div>

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
    });
    ",
    [
        'block' => 'scriptBottom'
    ]
);
?>