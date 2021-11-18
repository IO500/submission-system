<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;

/**
 * Releases Controller
 *
 * @property \App\Model\Table\ReleasesTable $Releases
 * @method \App\Model\Entity\Release[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ReleasesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $settings = [
            'order' => [
                'release_date' => 'DESC'
            ]
        ];

        $releases = $this->paginate($this->Releases, $settings);

        $this->set(compact('releases'));
    }

    /**
     * View method
     *
     * @param string|null $id Release id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $release = $this->Releases->get($id, [
            'contain' => [
                'Listings',
                'Submissions'
            ],
        ]);

        $types = $this->Releases->Listings->Types->find('all');

        $this->set(compact('release', 'types'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $release = $this->Releases->newEmptyEntity();

        if ($this->request->is('post')) {
            $release = $this->Releases->patchEntity($release, $this->request->getData());

            if ($this->Releases->save($release)) {
                $this->Flash->success(__('The release has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The release could not be saved. Please, try again.'));
        }

        $this->set(compact('release'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Release id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $release = $this->Releases->get($id, [
            'contain' => [],
        ]);

        if (date('Y-m-d') >= date('Y-m-d', strtotime($release->release_date->i18nFormat('yyyy-MM-dd') . ' +1 day'))) {
            $this->Flash->error(__('You are not allowed to modify a released list! Please, submit a public PR in GitHub!'));

            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->warning(__('Changing the release acronym will break existing refering URLs!'));            
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $release = $this->Releases->patchEntity($release, $this->request->getData());
            if ($this->Releases->save($release)) {
                $this->Flash->success(__('The release has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The release could not be saved. Please, try again.'));
        }

        $this->set(compact('release'));
    }

    /**
     * Synchronize method
     *
     * @return \Cake\Http\Response|null|void Redirects to index.
     */
    public function synchronize() {
        # We will re-create the view with all submissions
        $connection = ConnectionManager::get('default');

        # We need the name of each table that should be in the view (for valid and released lists)
        $releases = $this->Releases->find('all')
            ->where([
                'Releases.release_date <=' => date('Y-m-d')
            ])
            ->contain([
                'Listings' => [
                    'Types'
                ]
            ]);

        # Use a transaction to avoid data corruption
        $connection->begin();

        $connection->execute('DROP VIEW listings_submissions');

        $query = 'CREATE VIEW listings_submissions AS ';

        $lists = [];

        foreach ($releases as $release) {
            foreach ($release->listings as $listing) {
                $lists[] = 'SELECT * FROM list_' . str_replace('*', '_star', strtolower($release->acronym)) . '_' . str_replace(' ', '', strtolower($listing->type->name));
            }
        }

        $query .= implode(' UNION ALL ', $lists);

        $connection->execute($query);

        $connection->commit();

        $this->Flash->success(__('The releases have been synchronized!'));

        return $this->redirect(['action' => 'index']);
    }
}
