<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;

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
        $this->paginate = [
            'maxLimit' => 10000,
        ];
        $listing = $this->Listings->get($id, [
            'contain' => [
                'Types',
                'Releases',
            ],
        ]);

        // Prepare the real table name for this release
        $table = 'list_' . strtolower($listing->release->acronym) . '_' . str_replace('ten', '10node', strtolower($listing->type->url));
        $table = str_replace('-', '_', $table);

        // We need to dinamically link the model to the correct table
        $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
            'table' => $table,
        ]);

        $settings = [
            'order' => [
                'score' => 'DESC',
            ],
            'limit' => 10000,
        ];

        if (isset($this->request->getParam('?')['sort'])) {
            $settings['sortWhitelist'][] = $this->request->getParam('?')['sort'];
        }

        $submissions = $this->Listings->ListingsSubmissions->find('all')
            ->contain([
                'Submissions' => [
                    'Releases',
                ],
            ])
            ->where([
                'ListingsSubmissions.listing_id' => $listing->id,
            ]);

        $this->set('listing', $listing);
        $this->set('submissions', $this->paginate($submissions, $settings));
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
                    'Releases.id' => $request['release_id'],
                ])
                ->first();

            $type = $this->Listings->Types->find('all')
                ->where([
                    'Types.id' => $request['type_id'],
                ])
                ->first();

            $table = 'list_' . strtolower($release->acronym) . '_' . str_replace('ten', '10node', strtolower($type->url));
            $table = str_replace('-', '_', $table);

            // Create the table for this new release
            $this->create_table($table);

            // We need to dinamically link the model to the new table
            $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
                'table' => $table,
            ]);

            $selected_submissions = [];

            foreach ($request['selected'] as $submission_id) {
                array_push($selected_submissions, $submission_id);
            }

            $request['submissions']['_ids'] = $selected_submissions;

            $listing = $this->Listings->patchEntity($listing, $request, [
                'associated' => [
                    'Submissions',
                ],
            ]);
//dd($selected_submissions);
            if ($this->Listings->save($listing)) {
                // We need to update the scores in the join table
                $previous_release = $this->Listings->Submissions->Releases->find('all')
                    ->where([
                        'Releases.release_date <' => date('Y-m-d'),
                    ])
                    ->order([
                        'Releases.release_date' => 'DESC',
                    ])
                    ->first();
//dd($previous_release->toArray());
                // We need to update the link to the previous table
                $previous_table = 'list_' . strtolower($previous_release->acronym) . '_' . str_replace('ten', '10node', strtolower($type->url));
                $previous_table = str_replace('-', '_', $previous_table);

                $connection = ConnectionManager::get('default');

                $found = $connection->execute(
                    "SELECT 
                        COUNT(TABLE_NAME) AS total
                    FROM 
                        information_schema.TABLES 
                    WHERE
                        TABLE_SCHEMA = 'io500_db' AND
                        TABLE_NAME = '" . $previous_table . "'
                    "
                )->fetch('assoc')['total'];

                if (!$found) {
                    $previous_table = 'list_' . strtolower($previous_release->acronym) . '_historical';

                    // Get the historical list
                    $type = $this->Listings->Types->find('all')
                        ->where([
                            'Types.url' => 'historical',
                        ])
                        ->first();
                }

                // Unlink previous table
                \Cake\ORM\TableRegistry::remove('ListingsSubmissions');
                $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
                    'table' => $previous_table,
                ]);
//die($previous_table);
                // We need the id of the previous released list of the given type to build on it
                $previous_listing = $this->Listings->find('all')
                    ->contain([
                        'Releases',
                    ])
                    ->where([
                        'Listings.type_id' => $type->id,
                        'Releases.release_date <' => $release->release_date->i18nFormat('yyyy-MM-dd'),
                    ])
                    ->order([
                        'Releases.release_date' => 'DESC',
                    ])
                    ->first();
                // Fetch all submissions from the previous released list of this given type
                $submissions = $this->Listings->ListingsSubmissions->find('all')
                    ->contain([
                        'Submissions' => [
                            'Releases',
                        ],
                    ])
                    ->where([
                        'ListingsSubmissions.listing_id' => $previous_listing->id,
                    ])
                    ->order([
                        'ListingsSubmissions.score' => 'DESC',
                    ])
                    ->limit($limit);

                $scores = [];

                foreach ($submissions as $submission) {
                    // We will use the latest valid score to display
                    $scores[$submission->submission_id] = $submission->score;
                }

                // For new submissions in this release, we need to get the score from the submission table
                $new_submissions = $this->Listings->ListingsSubmissions->Submissions->find('all')
                    ->contain([
                        'Releases',
                    ])
                    ->where([
                        'Submissions.information_submission_date >=' => $previous_release->release_date->i18nFormat('yyyy-MM-dd'),
                    ])
                    ->limit($limit);
//dd($new_submissions);
                foreach ($new_submissions as $submission) {
                    // We will use the latest valid score to display
                    $scores[$submission->id] = $submission->io500_score;
                }
//dd($scores);
//die();
                // We need to dinamically link the model to the new table
                \Cake\ORM\TableRegistry::remove('ListingsSubmissions');
                $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
                    'table' => $table,
                ]);
//dd($table);
                $records = $this->Listings->ListingsSubmissions->find('all')
                    ->contain([
                        'Submissions',
                    ])
                    ->where([
                        'ListingsSubmissions.listing_id' => $listing->id,
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

    /**
     * create_table method
     *
     * @param  string $table Table name.
     * @return void
     */
    private function create_table($table)
    {
        $db = ConnectionManager::get('default');
        $schema = new TableSchema($table);

        $schema
            ->addColumn('id', [
                'type' => 'integer',
            ])
            ->addColumn('listing_id', [
                'type' => 'integer',
                'length' => 11,
            ])
            ->addColumn('submission_id', [
                'type' => 'integer',
                'length' => 11,
            ])
            ->addColumn('score', [
                'type' => 'float',
            ])
            ->addConstraint('primary', [
                'type' => 'primary',
                'columns' => [
                    'id',
                ],
            ]);

        // Create the table using our configuration
        $queries = $schema->createSql($db);

        foreach ($queries as $sql) {
            $db->execute($sql);
        }
    }

    /**
     * delete_table method
     *
     * @param  string $table Table name.
     * @return bool
     */
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
                'Releases',
            ],
        ]);

        // Prepare the real table name for this release
        $table = strtolower('list_' . $listing->release->acronym . '_' . str_replace('ten', '10node', $listing->type->url));
        $table = str_replace('-', '_', $table);

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

    /**
     * Download method
     *
     * @param null $bof Release acronym.
     * @param null $url Type url.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function download($bof = null, $url = null)
    {
        $limit = 10000;

        $db = ConnectionManager::get('default');

        // Create a schema collection.
        $collection = $db->getSchemaCollection();

        // Get a single table (instance of Schema\TableSchema)
        $tableSchema = $collection->describe('submissions');

        // Get columns list from table
        $columns = $tableSchema->columns();

        $release = $this->Listings->Releases->find('all')
            ->contain([
                'Listings' => [
                    'Types',
                ],
            ])
            ->where([
                'Releases.acronym' => strtoupper($bof),
            ])
            ->first();

        $listing = $this->Listings->find('all')
            ->contain([
                'Types',
                'Releases',
            ])
            ->where([
                'Types.url' => $url,
                'Releases.acronym' => strtoupper($bof),
            ])
            ->first();

        $settings = [
            'order' => [
                'score' => 'DESC',
            ],
            'limit' => $limit,
            'maxLimit' => $limit,
        ];

        if (isset($this->request->getParam('?')['sort'])) {
            $settings['sortWhitelist'][] = $this->request->getParam('?')['sort'];
        }

        $table = 'list_' . strtolower($release->acronym) . '_' . str_replace('ten', '10node', strtolower($url));
        $table = str_replace('-', '_', $table);

        // We need to dinamically link the model to the new table
        \Cake\ORM\TableRegistry::remove('ListingsSubmissions');
        $this->Listings->ListingsSubmissions = $this->getTableLocator()->get('ListingsSubmissions', [
            'table' => $table,
        ]);

        $submissions = $this->Listings->ListingsSubmissions->find('all')
            ->contain([
                'Submissions' => [
                    'Releases',
                ],
            ])
            ->where([
                'ListingsSubmissions.listing_id' => $listing->id,
            ]);

        $submissions = $this->paginate($submissions, $settings);

        $records = [];

        foreach ($submissions as $i => $submission) {
            // Modify the fields to export the CSV correctly
            $submission->submission->id = $i + 1;
            $submission->submission->score = $submission->score;
            $submission->submission->release_id = $submission->submission->release->acronym;

            unset($submission->submission->release);

            $records[] = $submission->submission;
        }

        $columns[] = 'score';

        $filename = implode('-', [
            'io500',
            strtolower($bof),
            strtolower($url),
        ]) . '.csv';

        $this->set(compact('records'));
        $this->setResponse($this->getResponse()->withDownload($filename));
        $this->viewBuilder()
            ->setClassName('CsvView.Csv')
            ->setOptions([
                'header' => $columns,
                'serialize' => 'records',
            ]);
    }
}
