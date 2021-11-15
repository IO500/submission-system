<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\Database\Schema\TableSchema;

/**
 * Listings Controller
 *
 * @property \App\Model\Table\ListingsTable $Listings
 * @method \App\Model\Entity\Listing[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ListingsController extends AppController
{
    /**
     * View method
     *
     * @param string|null $id Listing id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $listing = $this->Listings->get($id, [
            'contain' => [
                'Types',
                'Releases'
            ],
        ]);

        // Prepare the real table name for this release
        $table = strtolower('list_' . $listing->release->acronym . '_' . $listing->type->url);

        // We need to dinamically link the model to the correct table
        $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
            'table' => $table
        ]);

        $listing = $this->Listings->get($id, [
            'contain' => [
                'Types',
                'Releases',
                'Submissions' => [
                    'Releases'
                ]
            ],
        ]);

        $this->set(compact('listing'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $limit = 1000;

        $listing = $this->Listings->newEmptyEntity();

        if ($this->request->is('post')) {
            $request = $this->request->getData();            

            // Find the release for this listing
            $release = $this->Listings->Releases->find('all')
                ->where([
                    'Releases.id' => $request['release_id']
                ])
                ->first();

            $type = $this->Listings->Types->find('all')
                ->where([
                    'Types.id' => $request['type_id']
                ])
                ->first();

            $table = 'list_' . strtolower($release->acronym) . '_' . strtolower($type->url);

            // Create the table for this new release
            $this->create_table($table);

            // We need to dinamically link the model to the new table
            $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
                'table' => $table
            ]);

            $selected_submissions = [];

            foreach ($request['selected'] as $submission_id) {
                array_push($selected_submissions, $submission_id);
            }

            $request['submissions']['_ids'] = $selected_submissions;

            $listing = $this->Listings->patchEntity($listing, $request, [
                'associated' => [
                    'Submissions'
                ]
            ]);
//dd($selected_submissions);
            if ($this->Listings->save($listing)) {
                // We need to update the scores in the join table
                $previous_release = $this->Listings->Submissions->Releases->find('all')
                    ->where([
                        'Releases.release_date <' => date('Y-m-d')
                    ])
                    ->order([
                        'Releases.release_date' => 'DESC'  
                    ])
                    ->first();
//dd($previous_release->toArray());
                // We need to update the link to the previous table
                $previous_table = 'list_' . strtolower($previous_release->acronym) . '_' . strtolower($type->url);
                
                // Unlink previous table
                \Cake\ORM\TableRegistry::remove('ListingsSubmissions');
                $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
                    'table' => $previous_table
                ]);
//die($previous_table);
                // We need the id of the previous released list of the given type to build on it
                $previous_listing = $this->Listings->find('all')
                    ->contain([
                        'Releases',
                    ])
                    ->where([
                        'Listings.type_id' => $type->id,
                        'Releases.release_date <' => $release->release_date->i18nFormat('yyyy-MM-dd')
                    ])
                    ->order([
                        'Releases.release_date' => 'DESC',
                    ])
                    ->first();
//dd($previous_listing->toArray());
                // Fetch all submissions from the previous released list of this given type
                $submissions = $this->Listings->ListingsSubmissions->find('all')
                    ->contain([
                        'Submissions' => [
                            'Releases'
                        ]
                    ])
                    ->where([
                        'ListingsSubmissions.listing_id' => $previous_listing->id
                    ])
                    ->order([
                        'ListingsSubmissions.score' => 'DESC',
                    ])
                    ->limit($limit);

                $scores = [];
//dd($submissions->toArray());
                foreach ($submissions as $submission) {
                    // We will use the latest valid score to display
                    $scores[$submission->id] = $submission->score;
                }
//dd($scores);
                // For new submissions in this release, we need to get the score from the submission table
                $new_submissions = $this->Listings->ListingsSubmissions->Submissions->find('all')
                    ->contain([
                        'Releases'
                    ])
                    ->where([
                        'Submissions.information_submission_date >=' => $previous_release->release_date->i18nFormat('yyyy-MM-dd')
                    ])
                    ->limit($limit);
//dd($new_submissions);
                foreach ($new_submissions as $submission) {
                    // We will use the latest valid score to display
                    $scores[$submission->id] = $submission->original_io500_score;
                }
//dd($scores);
//die();
                // We need to dinamically link the model to the new table
                \Cake\ORM\TableRegistry::remove('ListingsSubmissions');
                $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
                    'table' => $table
                ]);
//dd($table);
                $records = $this->Listings->ListingsSubmissions->find('all')
                    ->contain([
                        'Submissions'
                    ])
                    ->where([
                        'ListingsSubmissions.listing_id' => $listing->id
                    ]);
//dd($records->toArray());
//die();
                foreach ($records as $record) {
                    $record->score = $scores[$record->submission->id];
                }

                $this->Listings->ListingsSubmissions->saveMany($records);

                $this->Flash->success(__('The listing has been saved.'));

                return $this->redirect(['controller' => 'releases', 'action' => 'view', $listing->release_id]);
            }

            $this->Flash->error(__('The listing could not be saved. Please, try again.'));
        }

        return $this->redirect(['controller' => 'releases', 'action' => 'index']);
    }

    private function create_table($table)
    {
        $db = ConnectionManager::get('default');
        $schema = new TableSchema($table);

        $schema
            ->addColumn('id', [
                'type' => 'integer'
            ])
            ->addColumn('listing_id', [
                'type' => 'integer',
                'length' => 11
            ])
            ->addColumn('submission_id', [
                'type' => 'integer',
                'length' => 11
            ])
            ->addColumn('score', [
                'type' => 'float'
            ])
            ->addConstraint('primary', [
                'type' => 'primary',
                'columns' => [
                    'id'
                ]
            ]);

        // Create the table using our configuration
        $queries = $schema->createSql($db);

        foreach ($queries as $sql) {
            $db->execute($sql);
        }
    }

    private function delete_table($table)
    {
        $db = ConnectionManager::get('default');
        $schema = new TableSchema($table);

        $queries = $schema->dropSql($db);

        foreach ($queries as $sql) {
            $db->execute($sql);
        }

        return true;
    }

    /**
     * Edit method
     *
     * @param string|null $id Listing id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    /*public function edit($id = null)
    {
        $listing = $this->Listings->get($id, [
            'contain' => [
                'Releases',
                'Submissions'
            ],
        ]);

        if (date('Y-m-d') > $listing->release->release_date->i18nFormat('yyyy-MM-dd')) {
            $this->Flash->error(__('You are not allowed to modify an already released list! Please, submit a public PR in GitHub!'));

            return $this->redirect(['action' => 'index']);
        }
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $listing = $this->Listings->patchEntity($listing, $this->request->getData());
        
            if ($this->Listings->save($listing)) {
                $this->Flash->success(__('The listing has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The listing could not be saved. Please, try again.'));
        }
        
        $types = $this->Listings->Types->find('list', ['limit' => 200]);
        $releases = $this->Listings->Releases->find('list', ['limit' => 200]);
        $submissions = $this->Listings->Submissions->find('list', ['limit' => 200]);
        
        $this->set(compact('listing', 'types', 'releases', 'submissions'));
    }*/

    /**
     * Delete method
     *
     * @param string|null $id Listing id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        $listing = $this->Listings->get($id, [
            'contain' => [
                'Types',
                'Releases'
            ],
        ]);

        // Prepare the real table name for this release
        $table = strtolower('list_' . $listing->release->acronym . '_' . $listing->type->url);

        if (date('Y-m-d') > $listing->release->release_date->i18nFormat('yyyy-MM-dd')) {
            $this->Flash->error(__('You are not allowed to delete an already released list!'));

            return $this->redirect(['action' => 'index']);
        }

        $listing = $this->Listings->get($id);
        
        if ($this->Listings->delete($listing)) {
            $this->delete_table($table);

            $this->Flash->success(__('The listing has been deleted.'));
        } else {
            $this->Flash->error(__('The listing could not be deleted. Please, try again.'));
        }

        return $this->redirect(['controller' => 'releases', 'action' => 'index']);
    }
}