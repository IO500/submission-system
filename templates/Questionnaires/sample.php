<div class="submissions index content">
    <h2><?php echo __('REPRODUCIBILITY') ?></h2>

    <div class="both"></div>
    
    <p>
        The goals of these questions are to demonstrate that your IO500 benchmark execution is valid, can be reproduced, and to provide additional details of your submitted storage system. Along with the other submitted items, the answers to these questions are used to calculate your reproducibility score and whether the submission is eligible for the Production list or Research list.
    </p>

    <p>
        <i class="fa-solid fa-circle-exclamation red-stripe"></i> All questions are <strong>mandatory</strong> and replies require a minimum of 10 words.
    </p>
</div>

<?php echo $this->element('reproducibility-questionnaire', ['view' => true]); ?>

<?php
echo $this->Html->script('tinymce/tinymce.min.js', [
     'referrerpolicy' => 'origin',
     'crossorigin' => 'anonymous'
]);
?>

<script type="text/javascript">
    var t_editors;

    editors = tinymce.init({
        license_key: 'gpl',
        statusbar: false,
        menubar: false,
        readonly: true,
        selector: 'textarea',
        height: 300,
        plugins: 'image link lists searchreplace table wordcount',
        toolbar: 'undo redo | bold italic underline strikethrough | link image table | numlist bullist indent outdent | removeformat',
        content_css: ['<?php echo $this->Url->build('/css/editor.css'); ?>']
    }).then(function(editors) {
        t_editors = editors;
    });

    document.getElementById('submit-site').addEventListener('click', function(e) {
        e.preventDefault();
    });
</script>