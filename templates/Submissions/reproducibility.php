<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>

<div id="editor"></div>

<?php 
echo $this->Form->input('json', 
	[
		'label' => false,
		'type' => 'textarea',
		'hidden' => 'true',
		'id' => 'json',
		'value' => file_get_contents(WWW_ROOT . '/js/template.json')
	]
);
?>

<?php
echo $this->Html->script(
	[
		"https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/header@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/paragraph@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/simple-image@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/list@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/raw@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/inline-code@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/code@latest",
		'./readonly.js',
		'./questionnaire.js'
	],
    [
        'block' => 'scriptBottom'
    ]
);
?>

<style type="text/css">
.ce-toolbar {
	display: none !important;
}
</style>