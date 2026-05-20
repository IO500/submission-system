<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

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
                'release_date' => 'DESC',
            ],
        ];

        $releases = $this->paginate($this->Releases, $settings);

        $next_release = $this->Releases->find('all')
            ->order([
                'Releases.release_date' => 'DESC',
            ])
            ->first();

        $this->set(compact('releases', 'next_release'));
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
                'Submissions' => [
                    'Status'
                ]
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

            // Auto-populate the checklist from the most recent existing release.
            // Labels and order are kept; statuses reset to pending so the new
            // release starts fresh.
            $previous = $this->Releases->find()
                ->order(['release_date' => 'DESC'])
                ->first();
            if ($previous && !empty($previous->checklist)) {
                $template = [];
                foreach ($previous->checklist as $item) {
                    if (empty($item['key'])) {
                        continue;
                    }
                    $template[] = [
                        'key' => $item['key'],
                        'label' => $item['label'] ?? '',
                        'status' => 'pending',
                        'changed_by_id' => null,
                        'changed_at' => null,
                    ];
                }
                $release->set('checklist', $template);
            }

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
     * Checklist view — render the per-release checklist.
     *
     * @param string|null $id Release id.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function checklist($id = null)
    {
        $release = $this->Releases->get($id);
        $checklist = $release->checklist ?? [];

        $userIds = [];
        foreach ($checklist as $item) {
            if (!empty($item['changed_by_id'])) {
                $userIds[(string)$item['changed_by_id']] = true;
            }
        }

        $userNames = [];
        if ($userIds) {
            $users = TableRegistry::getTableLocator()->get('Users')
                ->find()
                ->select(['id', 'username', 'first_name', 'last_name'])
                ->where(['id IN' => array_keys($userIds)])
                ->all();
            foreach ($users as $user) {
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                $userNames[(string)$user->id] = $fullName !== '' ? $fullName : (string)$user->username;
            }
        }

        $this->set(compact('release', 'checklist', 'userNames'));
    }

    /**
     * Toggle a single checklist item's status (pending ↔ done) and stamp
     * the current user / timestamp.
     *
     * @param string|null $id Release id.
     * @return \Cake\Http\Response Redirects to checklist view.
     */
    public function toggleChecklistItem($id = null)
    {
        $this->request->allowMethod(['post']);

        $release = $this->Releases->get($id);
        $itemKey = (string)$this->request->getData('item_key');
        $checklist = $release->checklist ?? [];

        $found = false;
        foreach ($checklist as &$item) {
            if (($item['key'] ?? null) === $itemKey) {
                $identity = $this->request->getAttribute('identity');
                $item['status'] = ($item['status'] ?? 'pending') === 'done' ? 'pending' : 'done';
                $item['changed_by_id'] = $identity ? $identity->getIdentifier() : null;
                $item['changed_at'] = FrozenTime::now()->toIso8601String();
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $this->Flash->error(__('Checklist item not found.'));

            return $this->redirect(['action' => 'checklist', $id]);
        }

        $release->set('checklist', $checklist);

        if ($this->Releases->save($release)) {
            $this->Flash->success(__('Checklist item updated.'));
        } else {
            $this->Flash->error(__('The checklist item could not be saved. Please, try again.'));
        }

        return $this->redirect(['action' => 'checklist', $id]);
    }

    /**
     * Edit the checklist item list (add/remove/rename) for a release.
     * Preserves status/changed_by/changed_at for items whose key is unchanged.
     *
     * @param string|null $id Release id.
     * @return \Cake\Http\Response|null|void Redirects on save, renders form otherwise.
     */
    public function editChecklistItems($id = null)
    {
        $release = $this->Releases->get($id);
        $existing = $release->checklist ?? [];

        $existingByKey = [];
        foreach ($existing as $item) {
            if (!empty($item['key'])) {
                $existingByKey[$item['key']] = $item;
            }
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $rows = (array)$this->request->getData('items');
            usort($rows, function ($a, $b) {
                return ((int)($a['position'] ?? 0)) <=> ((int)($b['position'] ?? 0));
            });
            $newChecklist = [];
            $usedKeys = [];

            foreach ($rows as $row) {
                $label = trim((string)($row['label'] ?? ''));
                if ($label === '' || !empty($row['remove'])) {
                    continue;
                }

                $key = trim((string)($row['key'] ?? ''));
                if ($key === '') {
                    $key = strtolower(Text::slug($label, '-'));
                    if ($key === '') {
                        $key = 'item-' . (count($newChecklist) + 1);
                    }
                }

                $baseKey = $key;
                $suffix = 2;
                while (isset($usedKeys[$key])) {
                    $key = $baseKey . '-' . $suffix++;
                }
                $usedKeys[$key] = true;

                if (isset($existingByKey[$key])) {
                    $newChecklist[] = [
                        'key' => $key,
                        'label' => $label,
                        'status' => $existingByKey[$key]['status'] ?? 'pending',
                        'changed_by_id' => $existingByKey[$key]['changed_by_id'] ?? null,
                        'changed_at' => $existingByKey[$key]['changed_at'] ?? null,
                    ];
                } else {
                    $newChecklist[] = [
                        'key' => $key,
                        'label' => $label,
                        'status' => 'pending',
                        'changed_by_id' => null,
                        'changed_at' => null,
                    ];
                }
            }

            $release->set('checklist', $newChecklist);

            if ($this->Releases->save($release)) {
                $this->Flash->success(__('The checklist items have been saved.'));

                return $this->redirect(['action' => 'checklist', $id]);
            }

            $this->Flash->error(__('The checklist items could not be saved. Please, try again.'));
        }

        $this->set(compact('release'));
    }

    /**
     * Synchronize method
     *
     * @return \Cake\Http\Response|null|void Redirects to index.
     */
    public function synchronize()
    {
        # We will re-create the view with all submissions
        $connection = ConnectionManager::get('default');

        $found = $connection->execute(
            "SELECT
                COUNT(TABLE_NAME) AS total
            FROM
                information_schema.TABLES
            WHERE
                TABLE_SCHEMA = 'io500_db' AND
                TABLE_NAME = 'listings_submissions'
            "
        )->fetch('assoc')['total'];

        # Use a transaction to avoid data corruption
        $connection->begin();

        if ($found) {
            $connection->execute('DROP VIEW listings_submissions');
        }

        $releases = $connection->execute(
            "SELECT
                TABLE_NAME
            FROM
                information_schema.TABLES
            WHERE
                TABLE_SCHEMA = 'io500_db' AND
                TABLE_NAME LIKE 'list\_%'
            "
        )->fetchAll('assoc');

        $query = 'CREATE VIEW listings_submissions AS ';

        $lists = [];

        foreach ($releases as $release) {
            $lists[] = 'SELECT * FROM ' . $release['TABLE_NAME'];
        }

        $query .= implode(' UNION ALL ', $lists);

        $connection->execute($query);

        $connection->commit();

        $this->Flash->success(__('The releases have been synchronized!'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Synchronize method
     *
     * @return \Cake\Http\Response|null|void Redirects to index.
     */
    public function synchronize_broken()
    {
        # We will re-create the view with all submissions
        $connection = ConnectionManager::get('default');

        # We need the name of each table that should be in the view (for valid and released lists)
        $releases = $this->Releases->find('all')
            ->where([
                'Releases.release_date <=' => date('Y-m-d'),
            ])
            ->contain([
                'Listings' => [
                    'Types',
                ],
            ]);

        $found = $connection->execute(
            "SELECT 
                COUNT(TABLE_NAME) AS total
            FROM 
                information_schema.TABLES 
            WHERE
                TABLE_SCHEMA = 'io500_db' AND
                TABLE_NAME = 'listings_submissions'
            "
        )->fetch('assoc')['total'];

        # Use a transaction to avoid data corruption
        $connection->begin();

        if ($found) {
            $connection->execute('DROP VIEW listings_submissions');
        }

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
