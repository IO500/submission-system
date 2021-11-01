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
            timeout: 20000,
            icon: '',
            position: 'topCenter',
            transitionIn: 'flipInX',
            class: 'iziIO iziIOWarning'
        }); 
    });
    ",
    [
        'block' => 'scriptBottom'
    ]
);
?>