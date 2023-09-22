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

<script src="https://cdn.tiny.cloud/1/1q5sjjedyv15tfpn9b7cvojp4i72ahfneyqj7yrfu771hcu1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script type="text/javascript">
    var t_editors;

    editors = tinymce.init({
        skin: 'outside',
        icons: 'small',
        statusbar: false,
        menubar: false,
        readonly: true,
        selector: 'textarea',
        height: 300,
        plugins: 'image link lists searchreplace table wordcount',
        toolbar: 'undo redo | bold italic underline strikethrough | link image table | numlist bullist indent outdent | removeformat',
        content_css: ['/io-500-hub/css/editor.css']
    }).then(function(editors) {
        t_editors = editors;
    });

    document.getElementById('submit-site').addEventListener('click', function(e) {
        e.preventDefault();
    });
</script>