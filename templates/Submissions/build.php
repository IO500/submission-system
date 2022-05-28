<nav id="breadcrumb">
    <p>YOU ARE HERE</p>

    <?php
    $this->Breadcrumbs->add(__('LISTS'), ['controller' => 'submissions', 'action' => 'index']);
    $this->Breadcrumbs->add(__('BUILD'), ['controller' => 'submissions', 'action' => 'build']);

    echo $this->Breadcrumbs->render([], ['separator' => ' / ']);
    ?>
</nav>

<div class="submissions index content">
    <h2>LIST BUILDER</h2>

    <div class="both"></div>

    <fieldset>
        <legend>README</legend>

        <p>
            You are building a new list based on the <strong><?php echo strtoupper($release_acronym); ?></strong> - <strong><?php echo strtoupper($type_url); ?></strong> list with the new submissions. You cannot use this interface to build lists for previous releases. Based on the provided records, just <strong>select the ones that should be included in this new list</strong>.
        </p>

        <p>
            The system will <strong class="highlight">highlight</strong> all those submissions that are possilibly duplicated considering the (<strong>system</strong>, <strong>institution</strong>, <strong>filesystem</strong>) combination. You need to review and pick only the one that should be used in the next release.
        </p>

        <p>
            Make sure you <strong>check</strong> if the <strong>submission wants to be included in the list you are creating</strong>.
        </p>
    </fieldset>

    <?php
    echo $this->Form->create(null, [
        'url' => [
            'controller' => 'listings',
            'action' => 'add'
        ]
    ]);
    ?>

    <fieldset>
        <legend>LIST CONFIGURATION</legend>

        <?php
        echo $this->Form->control('release_id');
        echo $this->Form->control('type_id', ['readonly']);
        echo $this->Form->control('description', ['label' => 'Description*', 'type' => 'textarea', 'required' => 'required', 'placeholder' => 'Please, provide a shot description about this list release.']);
        ?>
    </fieldset>


    <div class="table-responsive custom-table">
        <table class="tb">
            <thead>
                <tr>
                    <th rowspan="2" class="tb-id"></th>
                    <th rowspan="2" class="tb-id"></th>
                    <th rowspan="2" class="tb-id">#</th>
                    <th rowspan="2"></th>
                    <th colspan="4" class="tb-center">Information</th>
                    <th colspan="3" class="tb-center">IO500</th>
                    <th rowspan="2" class="tb-center"><?php echo $this->Paginator->sort('information_10_node_challenge', 'IO500') ?></th>
                    <th rowspan="2" class="tb-center"><?php echo $this->Paginator->sort('include_in_io500', '10-NODE') ?></th>
                </tr>
                <tr>
                    <th>System</th>
                    <th>Institution</th>
                    <th>Filesystem</th>
                    <th>Nodes</th>
                    <th rowspan="2" class="tb-number">SCORE</th>
                    <th rowspan="2" class="tb-number">BW<br/>(GIB/S)</th>
                    <th rowspan="2" class="tb-number">MD<br/>(KIOP/S)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $i => $submission) { ?>
                <tr class="<?php echo $submission->is_duplicated ? 'highlight' : ''; ?>">
                    <td class="tb-id">
                        <?php
                        echo $this->Form->checkbox('selected[]', ['value' => $submission->id, 'checked' => !$submission->is_new]);
                        echo $this->Form->control('score[]', ['value' => $submission->io500_score, 'type' => 'hidden']);
                        ?>
                    </td>
                    <td class="tb-id">
                        <?php if ($submission->release->acronym == strtoupper($release_acronym)) { ?>
                            <strong class="new-submission">NEW</strong>
                        <?php } ?>
                    </td>
                    <td class="tb-id">
                        <?php
                        echo $this->Html->link(($i + 1), [
                            'controller' => 'submissions',
                            'action' => 'view',
                            $submission->id
                        ], [
                            'class' => 'rank'
                        ]);
                        ?>
                    </td>
                    <td><?php echo $submission->has('release') ? $submission->release->acronym : '' ?></td>
                    <td><?php echo h($submission->information_system) ?></td>
                    <td><?php echo h($submission->information_institution) ?></td>
                    <td><?php echo h($submission->information_filesystem_type) ?></td>
                    <td><?php echo h($submission->information_client_nodes) ?></td>
                    <td class="tb-number"><?php echo $this->Number->format($submission->io500_score, ['places' => 2, 'precision' => 2]) ?></td>
                    <td class="tb-number"><?php echo $this->Number->format($submission->io500_bw, ['places' => 2, 'precision' => 2]) ?></td>
                    <td class="tb-number"><?php echo $this->Number->format($submission->io500_md, ['places' => 2, 'precision' => 2]) ?></td>
                    <td class="tb-center">
                        <?php
                        if ($submission->release->acronym == strtoupper($release_acronym)) {
                            if ($submission->information_10_node_challenge) {
                        ?>
                            <i class="fas fa-check"></i>
                        <?php
                            } else {
                        ?>
                            <i class="fas fa-ban"></i>
                        <?php
                            }
                        }
                        ?>
                    </td>
                    <td class="tb-center">
                        <?php 
                        if ($submission->release->acronym == strtoupper($release_acronym)) {
                            if ($submission->include_in_io500) {
                        ?>
                            <i class="fas fa-check"></i>
                        <?php 
                            } else {
                        ?>
                            <i class="fas fa-ban"></i>
                        <?php
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <fieldset>
        <legend>REVIEW & SAVE</legend>

        <p>Please, review your selection and once it looks ok, remember to save it! The saved list will not be avaible in the website until it is not released.</p>

        <div class="form-buttons">
            <?php
            echo $this->Form->submit(__('Save'));
            ?>
        </div>
    </fieldset>

    <?php
    echo $this->Form->end();
    ?>
</div>

<script type="text/javascript">
$("td").click(function(e) {
    var chk = $(this).closest("tr").find("input:checkbox").get(0);
    if(e.target != chk)
    {
        chk.checked = !chk.checked;
    }
});
</script>