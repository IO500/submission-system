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
    <li class="complete"><?php
        echo $this->AuthLink->link(__('Benchmark Results'),
            [
                'controller' => 'Submissions',
                'action' => 'results',
                $submission->id
            ]
        );
        ?>
    </li>
    <li class="active">Reproducibility Questionnaire</li>
    <li class="">Confirmation</li>
</ul>

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

<?php echo $this->element('reproducibility-questionnaire'); ?>

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
        selector: 'textarea',
        height: 300,
        setup: function (editor) {
            editor.on('change', function (e) {
                editor.save();
            });
        },
        plugins: 'image link lists searchreplace table wordcount',
        toolbar: 'undo redo | bold italic underline strikethrough | link image table | numlist bullist indent outdent | removeformat',
        content_css: ['<?php echo $this->Url->build('/css/editor.css'); ?>']
    }).then(function(editors) {
        t_editors = editors;
    });

    function wordCount(str) {
        return str.trim().split(/\s+/).length;
    }

    function validate() {
        var valid = true;
        var first = false;

        t_editors.forEach(function(editor) {
            var words = editor.contentDocument.body.innerHTML;

            if (wordCount(words) < 10) {
                document.getElementById(editor.id).closest('fieldset').style.border = '#d63b1e solid 1px';
                document.getElementById(editor.id).closest('fieldset').style.color = '#d63b1e';

                if (!first) {
                    editor.focus();

                    document.getElementById(editor.id).closest('fieldset').scrollIntoView({
                        behavior: 'smooth'
                    });

                    alert('All fields are mandatory and require a minimum of 10 words. Please, provide a complete response for all fields in red.')

                    first = true;
                }

                valid = false;
            } else {
                document.getElementById(editor.id).closest('fieldset').style.border = '#bbb solid 1px'; 
                document.getElementById(editor.id).closest('fieldset').style.color = '#454545';               
            }
        });

        return valid;
    }

    document.getElementById('submit-site').addEventListener('click', function(e) {
        var isValid = validate();

        if (!isValid) {
            e.preventDefault();
        }
    });
</script>