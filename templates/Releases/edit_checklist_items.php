<?php
$existing = $release->checklist ?? [];
// Initial blank rows; "ADD ROW" can append more.
$blankRows = 5;
$totalRows = count($existing) + $blankRows;
?>
<div class="row">
    <div class="column-responsive column-80">
        <div class="releases form content">
            <?php echo $this->Form->create(null, ['url' => ['action' => 'editChecklistItems', $release->id]]); ?>

            <fieldset>
                <legend><?php echo __('Edit Checklist Items') ?> &mdash; <?php echo h($release->acronym) ?></legend>

                <p><?php echo __('Drag rows to reorder. Renaming a label preserves its status. Remove an item by checking "Remove".') ?></p>

                <table class="tb checklist-edit">
                    <colgroup>
                        <col style="width: 70%;">
                        <col style="width: 20%;">
                        <col style="width: 10%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th><?php echo __('Label') ?></th>
                            <th><?php echo __('Current status') ?></th>
                            <th><?php echo __('Remove') ?></th>
                        </tr>
                    </thead>
                    <tbody id="checklist-items-body">
                        <?php $i = 0; foreach ($existing as $item) {
                            $st = ($item['status'] ?? 'pending') === 'done' ? 'done' : 'pending';
                        ?>
                            <tr draggable="true" class="checklist-row">
                                <td>
                                    <div class="checklist-row-cell">
                                        <span class="drag-handle" title="<?php echo __('Drag to reorder') ?>"><i class="fa-solid fa-arrows-up-down"></i></span>
                                        <?php echo $this->Form->text("items.{$i}.label", ['value' => $item['label'] ?? '', 'class' => 'checklist-label-input']) ?>
                                    </div>
                                    <?php echo $this->Form->hidden("items.{$i}.position", ['value' => $i, 'class' => 'item-position']) ?>
                                    <?php echo $this->Form->hidden("items.{$i}.key", ['value' => $item['key'] ?? '']) ?>
                                </td>
                                <td>
                                    <strong class="status status-<?php echo $st ?>"><?php echo $st === 'done' ? __('Done') : __('Pending') ?></strong>
                                </td>
                                <td><?php echo $this->Form->checkbox("items.{$i}.remove") ?></td>
                            </tr>
                        <?php $i++; } ?>

                        <?php for ($j = 0; $j < $blankRows; $j++, $i++) { ?>
                            <tr draggable="true" class="checklist-row">
                                <td>
                                    <div class="checklist-row-cell">
                                        <span class="drag-handle" title="<?php echo __('Drag to reorder') ?>"><i class="fa-solid fa-arrows-up-down"></i></span>
                                        <?php echo $this->Form->text("items.{$i}.label", ['value' => '', 'placeholder' => __('New item label'), 'class' => 'checklist-label-input']) ?>
                                    </div>
                                    <?php echo $this->Form->hidden("items.{$i}.position", ['value' => $i, 'class' => 'item-position']) ?>
                                    <?php echo $this->Form->hidden("items.{$i}.key", ['value' => '']) ?>
                                </td>
                                <td><strong class="status status-new"><?php echo __('New') ?></strong></td>
                                <td></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <div class="form-buttons">
                    <button type="button" id="add-checklist-row"><?php echo __('ADD ROW') ?></button>
                    <?php echo $this->Form->button(__('SAVE')) ?>
                    <?php echo $this->Html->link(__('CANCEL'), ['action' => 'checklist', $release->id], ['class' => 'button']) ?>
                </div>
            </fieldset>

            <?php echo $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
(function () {
    var tbody = document.getElementById('checklist-items-body');
    if (!tbody) return;
    var dragged = null;
    var nextIndex = <?php echo (int)$totalRows; ?>;

    function renumber() {
        var rows = tbody.querySelectorAll('tr.checklist-row');
        for (var idx = 0; idx < rows.length; idx++) {
            var input = rows[idx].querySelector('.item-position');
            if (input) input.value = idx;
        }
    }

    tbody.addEventListener('dragstart', function (e) {
        var tr = e.target.closest('tr.checklist-row');
        if (!tr) return;
        dragged = tr;
        tr.style.opacity = '0.4';
        e.dataTransfer.effectAllowed = 'move';
    });

    tbody.addEventListener('dragend', function () {
        if (dragged) dragged.style.opacity = '';
        dragged = null;
        renumber();
    });

    tbody.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    });

    tbody.addEventListener('drop', function (e) {
        e.preventDefault();
        if (!dragged) return;
        var tr = e.target.closest('tr.checklist-row');
        if (!tr || tr === dragged) return;
        var rect = tr.getBoundingClientRect();
        var before = (e.clientY - rect.top) < rect.height / 2;
        tbody.insertBefore(dragged, before ? tr : tr.nextSibling);
    });

    var addBtn = document.getElementById('add-checklist-row');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            var i = nextIndex++;
            var tr = document.createElement('tr');
            tr.draggable = true;
            tr.className = 'checklist-row';
            tr.innerHTML =
                '<td>' +
                    '<div class="checklist-row-cell">' +
                        '<span class="drag-handle" title="<?php echo __('Drag to reorder') ?>"><i class="fa-solid fa-arrows-up-down"></i></span>' +
                        '<input type="text" class="checklist-label-input" name="items[' + i + '][label]" placeholder="<?php echo __('New item label') ?>" value="">' +
                    '</div>' +
                    '<input type="hidden" name="items[' + i + '][position]" value="' + i + '" class="item-position">' +
                    '<input type="hidden" name="items[' + i + '][key]" value="">' +
                '</td>' +
                '<td><strong class="status status-new"><?php echo __('New') ?></strong></td>' +
                '<td></td>';
            tbody.appendChild(tr);
            renumber();
        });
    }
})();
</script>
