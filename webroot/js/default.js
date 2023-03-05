$(document).ready(function() {
    if ($('#custom-fields').length > 0) {
        $('#custom-fields').selectize({
            plugins: [
                'remove_button',
                'restore_on_backspace'
            ],
            delimiter: ',',
            persist: false,
            valueField: 'id',
            labelField: 'name',
            searchField: [
                'id',
                'name',
            ]
        });

        $('#custom-order').selectize({
            persist: false,
            valueField: 'id',
            labelField: 'name',
            searchField: [
                'id',
                'name',
            ]
        });
    }

    $('li.complete').click(function(e) {
        $(this).children('a').get(0).click();
    });
}); 
