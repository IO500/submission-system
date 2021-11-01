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
        ];

        $submissions = $this->paginate($this->Submissions);

        $this->set(compact('submissions'));
    }

    public function mine()
    {
        $userID = $this->getRequest()->getAttribute('identity')['id'] ?? null;
        
        $query = $this->Submissions->findAllByUserId($userID);

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

                $submission->information_institution = $json['DATA']['att']['institution'];
                $submission->information_storage_vendor = $json['DATA']['childs'][2]['att']['vendor'];

                $submission->information_system = $json['DATA']['childs'][1]['att']['name'];

                $submission->io500_score = $json['DATA']['childs'][0]['att']['score'];
                $submission->io500_bw = $json['DATA']['childs'][0]['att']['scoreBW'][0];
                $submission->io500_md = $json['DATA']['childs'][0]['att']['scoreMD'][0];

                $submission->information_client_nodes = $json['DATA']['childs'][0]['childs'][0]['att']['clientNodes'];
                $submission->information_client_procs_per_node = $json['DATA']['childs'][0]['childs'][0]['att']['procsPerNode']; 

                $submission->ior_easy_write = $json['DATA']['childs'][0]['childs'][1]['att']['easy write'][0];
                $submission->ior_easy_read = $json['DATA']['childs'][0]['childs'][1]['att']['easy read'][0];
                $submission->ior_hard_write = $json['DATA']['childs'][0]['childs'][1]['att']['hard write'][0];
                $submission->ior_hard_read = $json['DATA']['childs'][0]['childs'][1]['att']['hard read'][0];

                $submission->mdtest_easy_write = $json['DATA']['childs'][0]['childs'][2]['att']['easy write'][0];
                $submission->mdtest_easy_stat = $json['DATA']['childs'][0]['childs'][2]['att']['easy stat'][0];
                $submission->mdtest_easy_delete = $json['DATA']['childs'][0]['childs'][2]['att']['easy delete'][0];
                $submission->mdtest_hard_write = $json['DATA']['childs'][0]['childs'][2]['att']['hard write'][0];
                $submission->mdtest_hard_stat = $json['DATA']['childs'][0]['childs'][2]['att']['hard stat'][0];
                $submission->mdtest_hard_read = $json['DATA']['childs'][0]['childs'][2]['att']['hard read'][0];
                $submission->mdtest_hard_delete = $json['DATA']['childs'][0]['childs'][2]['att']['hard delete'][0];

                $submission->find_mixed = $json['DATA']['childs'][0]['childs'][3]['att']['mixed'][0];

                $submission->information_client_operating_system = $json['DATA']['childs'][1]['childs'][0]['att']['distribution'];
                $submission->information_client_operating_system_version = $json['DATA']['childs'][1]['childs'][0]['att']['distribution version'];

                dd($submission);
                //dd($json);die();

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
        if (date('Y-m-d') > $submission->release->release_date) {
            $this->Flash->error(__('This submission was already released in a list. To modify its metadata, open a GitHub pull request with the change.'));

            return $this->redirect(['action' => 'mine']);
        }

        $this->Flash->warning(__('Notice that if you re-upload your system information JSON file, all changes will reflect the current file state.'));

        if ($this->request->is(['patch', 'post', 'put'])) {
            $submission = $this->Submissions->patchEntity($submission, $this->request->getData());

            if ($this->Submissions->save($submission)) {

                $string = file_get_contents(ROOT . DS . $submission->result_tar_dir . $submission->result_tar);
                $json = json_decode($string, true);

                $submission->information_institution = $json['DATA']['att']['institution'];
                $submission->information_storage_vendor = $json['DATA']['childs'][2]['att']['vendor'];

                $submission->information_system = $json['DATA']['childs'][1]['att']['name'];

                $submission->io500_score = $json['DATA']['childs'][0]['att']['score'];
                $submission->io500_bw = $json['DATA']['childs'][0]['att']['scoreBW'][0];
                $submission->io500_md = $json['DATA']['childs'][0]['att']['scoreMD'][0];

                $submission->information_client_nodes = $json['DATA']['childs'][0]['childs'][0]['att']['clientNodes'];
                $submission->information_client_procs_per_node = $json['DATA']['childs'][0]['childs'][0]['att']['procsPerNode']; 

                $submission->ior_easy_write = $json['DATA']['childs'][0]['childs'][1]['att']['easy write'][0];
                $submission->ior_easy_read = $json['DATA']['childs'][0]['childs'][1]['att']['easy read'][0];
                $submission->ior_hard_write = $json['DATA']['childs'][0]['childs'][1]['att']['hard write'][0];
                $submission->ior_hard_read = $json['DATA']['childs'][0]['childs'][1]['att']['hard read'][0];

                $submission->mdtest_easy_write = $json['DATA']['childs'][0]['childs'][2]['att']['easy write'][0];
                $submission->mdtest_easy_stat = $json['DATA']['childs'][0]['childs'][2]['att']['easy stat'][0];
                $submission->mdtest_easy_delete = $json['DATA']['childs'][0]['childs'][2]['att']['easy delete'][0];
                $submission->mdtest_hard_write = $json['DATA']['childs'][0]['childs'][2]['att']['hard write'][0];
                $submission->mdtest_hard_stat = $json['DATA']['childs'][0]['childs'][2]['att']['hard stat'][0];
                $submission->mdtest_hard_read = $json['DATA']['childs'][0]['childs'][2]['att']['hard read'][0];
                $submission->mdtest_hard_delete = $json['DATA']['childs'][0]['childs'][2]['att']['hard delete'][0];

                $submission->find_mixed = $json['DATA']['childs'][0]['childs'][3]['att']['midex'][0];

                $submission->information_client_operating_system = $json['DATA']['childs'][1]['childs'][0]['distribution'];
                $submission->information_client_operating_system_version = $json['DATA']['childs'][1]['childs'][0]['distribution version'];

                dd($submission);
                dd($json);die();

                $this->Submissions->save($submission);

                $this->Flash->success(__('The submission has been saved.'));

                return $this->redirect(['action' => 'mine']);
            }

            $this->Flash->error(__('The submission could not be saved. Please, try again.'));
        }

        $releases = $this->Submissions->Releases->find('list', ['limit' => 200]);

        $this->set(compact('submission', 'releases'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Submission id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $submission = $this->Submissions->get($id);
        if ($this->Submissions->delete($submission)) {
            $this->Flash->success(__('The submission has been deleted.'));
        } else {
            $this->Flash->error(__('The submission could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
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
                'Releases.release_date <' => $last_release->release_date->i18nFormat('yyyy-MM-dd') //date('Y-m-d'),
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
            $submission->io500_score = $submission->score;

            $submission->is_new = true;

            $key = $submission->information_system . '-' . $submission->information_institution . '-' . $submission->information_filesystem_type;

            if (in_array($key, $duplicated)) {
                $submission->is_duplicated = true;
            }

            $duplicated[] = $key;

            $records[] = $submission;
        }

        $this->set('types', $types);
        $this->set('releases', $releases);
        $this->set('submissions', $records);
    }
}
