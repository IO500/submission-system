<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * ReproducibilityScores Controller
 *
 * @property \App\Model\Table\ReproducibilityScoresTable $ReproducibilityScores
 * @method \App\Model\Entity\ReproducibilityScore[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ReproducibilityScoresController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $reproducibilityScores = $this->paginate($this->ReproducibilityScores);

        $this->set(compact('reproducibilityScores'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $reproducibilityScore = $this->ReproducibilityScores->newEmptyEntity();

        if ($this->request->is('post')) {
            $reproducibilityScore = $this->ReproducibilityScores->patchEntity($reproducibilityScore, $this->request->getData());

            if ($this->ReproducibilityScores->save($reproducibilityScore)) {
                $this->Flash->success(__('The reproducibility score has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The reproducibility score could not be saved. Please, try again.'));
        }

        $this->set(compact('reproducibilityScore'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Reproducibility Score id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $reproducibilityScore = $this->ReproducibilityScores->get($id, [
            'contain' => [],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $reproducibilityScore = $this->ReproducibilityScores->patchEntity($reproducibilityScore, $this->request->getData());

            if ($this->ReproducibilityScores->save($reproducibilityScore)) {
                $this->Flash->success(__('The reproducibility score has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The reproducibility score could not be saved. Please, try again.'));
        }

        $this->set(compact('reproducibilityScore'));
    }
}
