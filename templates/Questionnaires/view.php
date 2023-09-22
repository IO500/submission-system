<div class="submissions index content">
    <h2>SUBMISSION #<?php echo h($submission->id) ?></h2>

    <div class="both"></div>
    
    <h3><?php echo __('REPRODUCIBILITY QUESTIONNAIRE') ?></h3>
</div>

<?php echo $this->element('reproducibility-questionnaire'); ?>

<script src="https://cdn.tiny.cloud/1/1q5sjjedyv15tfpn9b7cvojp4i72ahfneyqj7yrfu771hcu1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script type="text/javascript">
    var t_editors;

    editors = tinymce.init({
        skin: 'outside',
        icons: 'small',
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
        content_css: ['/io-500-hub/css/editor.css']
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
