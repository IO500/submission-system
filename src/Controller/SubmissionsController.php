<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\Filesystem\File;
use NXP\Exception\IncorrectBracketsException;
use NXP\Exception\IncorrectExpressionException;
use NXP\Exception\UnknownOperatorException;
use NXP\Exception\UnknownVariableException;
use NXP\MathExecutor;

/**
 * Submissions Controller
 *
 * @property \App\Model\Table\SubmissionsTable $Submissions
 * @method \App\Model\Entity\Submission[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SubmissionsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => [
                'Releases'
            ],
            'order' => [
                'Submissions.id' => 'DESC'
            ]
        ];

        $submissions = $this->paginate($this->Submissions);

        $this->set(compact('submissions'));
    }

    public function mine()
    {
        $userID = $this->getRequest()->getAttribute('identity')['id'] ?? null;

        $query = $this->Submissions->find('all')
            ->where([
                'Submissions.user_id' => $userID
            ]);

        $this->paginate = [
            'contain' => [
                'Releases',
            ],
            'order' => [
                'id' => 'DESC'
            ]
        ];
        $submissions = $this->paginate($query);

        $this->set(compact('submissions'));
    }

    /**
     * View method
     *
     * @param string|null $id Submission id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $submission = $this->Submissions->get($id, [
            'contain' => [
                'Releases',
                'Listings'
            ]
        ]);

        $this->set(compact('submission'));
    }

    private function parse($submission, $json)
    {
        // Institution

        $json_site = $this->find_information($json, 'type', 'Site');

        $submission->information_institution = $json_site['att']['institution'];

        // Nodes

        $json_nodes = $this->find_information($json, 'type', 'Nodes')['att'];

        $submission->information_client_nodes = isset($json_nodes['count']) ? $json_nodes['count'] : null;

        // Information

        $json_information = $this->find_information($json, 'type', 'information')['att'];

        $submission->information_client_nodes = isset($json_information['clientNodes']) ? $json_information['clientNodes'] : $submission->information_client_nodes;
        $submission->information_client_procs_per_node = isset($json_information['procsPerNode']) ?  : null;
        $submission->information_client_total_procs = intval($submission->information_client_nodes) * intval($submission->information_client_procs_per_node);

        $submission->information_submission_date = isset($json_information['submission_date']) ? $json_information['submission_date'] : $submission->information_submission_date;

        // Supercomputer

        $json_site = $this->find_information($json, 'type', 'Supercomputer');

        $submission->information_system = isset($json_site['att']['name']) ? $json_site['att']['name'] : null;

        // File System

        $json_file_system = $this->find_information($json, 'type', 'file system');

        $submission->information_filesystem_vendor = isset($json_file_system['vendor']) ? $json_file_system['vendor'] : null;
        $submission->information_filesystem_name = isset($json_file_system['name']) ? $json_file_system['name'] : null;
        $submission->information_filesystem_type = isset($json_file_system['software']) ? $json_file_system['software'] : null;
        $submission->information_filesystem_version = isset($json_file_system['version']) ? $json_file_system['version'] : null;

        // IO500 metrics

        $json_io500 = $this->find_information($json, 'type', 'IO500');

        # Ensure support for the deprecated and new formats
        $new_format = True;

        if (is_array($json_io500['att']['scoreBW'])) {
            $new_format = False;
        }

        $submission->original_io500_score = isset($json_io500['att']['score']) ?  : null;

        if ($new_format) {
            $json_io500['att']['scoreBW'] = explode(' ', $json_io500['att']['scoreBW']);
            $json_io500['att']['scoreMD'] = explode(' ', $json_io500['att']['scoreMD']);
        }

        $submission->io500_bw = isset($json_io500['att']['scoreBW'][0]) ? $json_io500['att']['scoreBW'][0] : null;
        $submission->io500_md = isset($json_io500['att']['scoreMD'][0]) ? $json_io500['att']['scoreMD'][0] : null;

        // IOR metrics

        $json_ior = $this->find_information($json, 'type', 'IOR')['att'];

        if ($new_format) {
            $json_ior['easy write'] = explode(' ', $json_ior['easy write']);
            $json_ior['easy read'] = explode(' ', $json_ior['easy read']);
            $json_ior['hard write'] = explode(' ', $json_ior['hard write']);
            $json_ior['hard read'] = explode(' ', $json_ior['hard read']);   
        }
        $submission->ior_easy_write = isset($json_ior['easy write'][0]) ? $json_ior['easy write'][0] : null;
        $submission->ior_easy_read = isset($json_ior['easy read'][0]) ? $json_ior['easy read'][0] : null;
        $submission->ior_hard_write = isset($json_ior['hard write'][0]) ? $json_ior['hard write'][0] : null;
        $submission->ior_hard_read = isset($json_ior['hard read'][0]) ? $json_ior['hard read'][0] : null;

        // MDTest metrics

        $json_mdtest = $this->find_information($json, 'type', 'MDTest')['att'];

        if ($new_format) {
            $json_mdtest['easy write'] = explode(' ', $json_mdtest['easy write']);
            $json_mdtest['easy stat'] = explode(' ', $json_mdtest['easy stat']);
            $json_mdtest['easy delete'] = explode(' ', $json_mdtest['easy delete']);
            $json_mdtest['hard write'] = explode(' ', $json_mdtest['hard write']);
            $json_mdtest['hard stat'] = explode(' ', $json_mdtest['hard stat']);
            $json_mdtest['hard read'] = explode(' ', $json_mdtest['hard read']);
            $json_mdtest['hard delete'] = explode(' ', $json_mdtest['hard delete']);
        }
        $submission->mdtest_easy_write = isset($json_mdtest['easy write'][0]) ? $json_mdtest['easy write'][0] : null;
        $submission->mdtest_easy_stat = isset($json_mdtest['easy stat'][0]) ? $json_mdtest['easy stat'][0] : null;
        $submission->mdtest_easy_delete = isset($json_mdtest['easy delete'][0]) ? $json_mdtest['easy delete'][0] : null;
        $submission->mdtest_hard_write = isset($json_mdtest['hard write'][0]) ? $json_mdtest['hard write'][0] : null;
        $submission->mdtest_hard_stat = isset($json_mdtest['hard stat'][0]) ? $json_mdtest['hard stat'][0] : null;
        $submission->mdtest_hard_read = isset($json_mdtest['hard read'][0]) ? $json_mdtest['hard read'][0] : null;
        $submission->mdtest_hard_delete = isset($json_mdtest['hard delete'][0]) ? $json_mdtest['hard delete'][0] : null;

        // find metrics

        $json_find = $this->find_information($json, 'type', 'find')['att'];

        if ($new_format) {
            $json_find['mixed'] = explode(' ', $json_find['mixed']);
        }
        $submission->find_mixed = isset($json_find['mixed'][0]) ? $json_find['mixed'][0] : null;

        return $submission;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $submission = $this->Submissions->newEmptyEntity();

        $release = $this->Submissions->Releases->find('all')
            ->where([
                'Releases.release_date >' => date('Y-m-d')
            ]);

        if ($release->isEmpty()) {
            $this->Flash->error(__('There are no open calls for submission. Please, check again soon!'));

            return $this->redirect(['action' => 'mine']);
        }

        $release_id = $release->first()['id'];

        if ($this->request->is('post')) {
            $submission = $this->Submissions->patchEntity($submission, $this->request->getData());

            $submission->release_id = $release_id;
            $submission->user_id = $this->getRequest()->getAttribute('identity')['id'];
            $submission->information_submitter = $this->getRequest()->getAttribute('identity')['email'];
            $submission->information_submission_date = date('Y-m-d H:i:s');
            $submission->upload_hash = sha1($submission->user_id . $submission->information_submission_date);

            $submission->status = 'NEW';

            if ($this->Submissions->save($submission)) {
                $string = file_get_contents(ROOT . DS . $submission->system_information_dir . $submission->system_information);
                $json = json_decode($string, true);

                $submission = $this->parse($submission, $json);

                $this->Submissions->save($submission);

                $this->Flash->success(__('The submission has been saved.'));

                return $this->redirect(['action' => 'mine']);
            }

            $this->Flash->error(__('The submission could not be saved. Please, try again.'));
        }

        $this->set(compact('submission', 'release_id'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Submission id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $submission = $this->Submissions->get($id, [
            'contain' => [
                'Releases',
                'Listings'
            ],
        ]);

        // Only allow submissions that are 'new' to be modified. Once released, they should follow the GitHub PR flow.
        if (date('Y-m-d') > $submission->release->release_date->i18nFormat('yyyy-MM-dd')) {
            $this->Flash->error(__('This submission was already released in a list. To modify its metadata, open a GitHub pull request with the change.'));

            return $this->redirect(['action' => 'mine']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $previous_hash = sha1(file_get_contents(ROOT . DS . $submission->system_information_dir . $submission->system_information));

            $submission = $this->Submissions->patchEntity($submission, $this->request->getData());
            $submission->upload_hash = sha1($submission->user_id . $submission->information_submission_date);

            if ($this->Submissions->save($submission)) {

                $new_hash = sha1(file_get_contents(ROOT . DS . $submission->system_information_dir . $submission->system_information));
                
                if (true) { // $previous_hash != $new_hash) {
                    $string = file_get_contents(ROOT . DS . $submission->system_information_dir . $submission->system_information);
                    $json = json_decode($string, true);

                    $submission = $this->parse($submission, $json);

                    $this->Submissions->save($submission);
                }

                $this->Flash->success(__('The submission has been saved.'));
            } else {
                $this->Flash->error(__('The submission could not be saved. Please, try again.'));
            }
        }

        $this->Flash->warning(__('Notice that if you re-upload your system information JSON file, all changes will reflect the current file state.'));

        $releases = $this->Submissions->Releases->find('list', ['limit' => 200]);

        $this->set(compact('submission', 'releases'));
    }

    /**
     * Customize method
     * Allows to create custom lists based on the provided list type in the last release
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function build($release_acronym, $type_url)
    {
        $limit = 1000;

        // Fetch the release based on the provided acronym
        $release = $this->Submissions->Releases->find('all')
            ->where([
                'Releases.acronym' => strtoupper($release_acronym)
            ])
            ->first();

        // Fetch the list type based on the provided URL
        $type = $this->Submissions->Listings->Types->find('all')
            ->where([
                'Types.url' => $type_url
            ])
            ->first();

        // Fetch the only option for the select box
        $types = $this->Submissions->Listings->Types->find('list')
            ->where([
                'Types.url' => $type_url
            ]);

        // Find all releases
        $releases = $this->Submissions->Releases->find('list')
            ->where([
                'Releases.release_date >=' => date('Y-m-d'),
            ]);

        $last_release = $this->Submissions->Releases->find('all')
            ->where([
                'Releases.release_date <' => date('Y-m-d')
            ])
            ->order([
                'Releases.release_date' => 'DESC'  
            ])
            ->first();

        // We need the id of the previous released list of the given type to build on it
        $listing = $this->Submissions->Listings->find('all')
            ->contain([
                'Releases',
            ])
            ->where([
                'Listings.type_id' => $type->id,
                'Releases.release_date' => $last_release->release_date->i18nFormat('yyyy-MM-dd') //date('Y-m-d'),
            ])
            ->order([
                'Releases.release_date' => 'DESC',
            ])
            ->first();

        // Fetch all submissions from the previous released list of this given type
        $submissions = $this->Submissions->ListingsSubmissions->find('all')
            ->contain([
                'Submissions' => [
                    'Releases'
                ]
            ])
            ->where([
                'ListingsSubmissions.listing_id' => $listing->id
            ])
            ->order([
                'ListingsSubmissions.score' => 'DESC',
            ])
            ->limit($limit);

        $records = [];
        $duplicated = [];

        foreach ($submissions as $submission) {
            // We will use the latest valid score to display
            $submission->submission->io500_score = $submission->score;
            $submission->submission->information_list_name = $submission->submission->release->acronym;

            $submission->submission->is_new = false;

            // Check for duplicate values
            $key = $submission->submission->information_system . '-' . $submission->submission->information_institution . '-' . $submission->submission->information_filesystem_type;

            if (in_array($key, $duplicated)) {
                $submission->submission->is_duplicated = true;
            }

            $duplicated[] = $key;

            $records[] = $submission->submission;
        }

        // We now need to fecth only the new submissions, i.e. those make between the last list release and today
        $new_submissions = $this->Submissions->find('all')
            ->contain([
                'Releases'
            ])
            ->where([
                'Submissions.information_submission_date >=' => $listing->release->release_date
            ])
            ->limit($limit);

        foreach ($new_submissions as $submission) {
            // We will use the latest valid score to display
            $submission->is_new = true;

            $key = $submission->information_system . '-' . $submission->information_institution . '-' . $submission->information_filesystem_type;

            if (in_array($key, $duplicated)) {
                $submission->is_duplicated = true;
            }

            $duplicated[] = $key;

            $records[] = $submission;
        }

        // Sort based on the scoree
        uasort($records, [$this, 'sort']);

        $this->set('types', $types);
        $this->set('releases', $releases);
        $this->set('submissions', $records);
    }

    /**
     * sort method
     *
     * @param  object|null $a.
     * @param  object|null $b.
     * @return bool
     */
    private function sort($a, $b)
    {
        return $a->io500_score < $b->io500_score;
    }

    private function find_information($array, $key, $value)
    {
        $iterator = new \RecursiveArrayIterator($array);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($recursive as $k => $v) {
            if ($k === $key && $v == $value) {
                
                return $recursive->getSubIterator($recursive->getDepth() - 1)->current();
            }
        }

        return null;
    }

}
