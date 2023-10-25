<?php
echo $this->Html->css(
    [
        'iziToast.min.css'
    ]
);

echo $this->Html->script('iziToast.min.js',
    [
        'block' => 'scriptBottom'
    ]
);
$this->Html->scriptBlock(
    "
    $(document).ready(function() {
        iziToast.show({
            message: '{$message}',
            timeout: 100000,
            icon: 'fa fa-warning',
            position: 'topCenter',
            transitionIn: 'flipInX',
            class: 'iziIO iziIOValidation'
        }); 
    });
    ",
    [
        'block' => 'scriptBottom'
    ]
);
?>