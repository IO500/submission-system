<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Sqls Controller
 *
 * @method \App\Model\Entity\Sql[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SqlsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $sqls = $this->paginate($this->Sqls);

        $this->set(compact('sqls'));
    }

    /**
     * View method
     *
     * @param string|null $id Sql id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $sql = $this->Sqls->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('sql'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $sql = $this->Sqls->newEmptyEntity();
        if ($this->request->is('post')) {
            $sql = $this->Sqls->patchEntity($sql, $this->request->getData());
            if ($this->Sqls->save($sql)) {
                $this->Flash->success(__('The sql has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The sql could not be saved. Please, try again.'));
        }
        $this->set(compact('sql'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Sql id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $sql = $this->Sqls->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $sql = $this->Sqls->patchEntity($sql, $this->request->getData());
            if ($this->Sqls->save($sql)) {
                $this->Flash->success(__('The sql has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The sql could not be saved. Please, try again.'));
        }
        $this->set(compact('sql'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Sql id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $sql = $this->Sqls->get($id);
        if ($this->Sqls->delete($sql)) {
            $this->Flash->success(__('The sql has been deleted.'));
        } else {
            $this->Flash->error(__('The sql could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
