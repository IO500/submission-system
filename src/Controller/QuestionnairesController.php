<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Questionnaires Controller
 *
 * @property \App\Model\Table\QuestionnairesTable $Questionnaires
 * @method \App\Model\Entity\Questionnaire[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class QuestionnairesController extends AppController
{
    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add($submission_id)
    {
        $userID = $this->getRequest()->getAttribute('identity')['id'] ?? null;

        $submission = $this->Questionnaires->Submissions->get($submission_id, [
            'contain' => [
                'Releases',
                'Listings',
                'Questionnaires',
            ],
            //,
            //'conditions' => [
            //    'Submissions.user_id' => $userID
            //]
        ]);

        // Only allow submissions that are 'new' to be modified. Once released, they should follow the GitHub PR flow.
        if (date('Y-m-d') > $submission->release->release_date->i18nFormat('yyyy-MM-dd')) {
            $this->Flash->error(__('This submission was already released in a list. To modify its metadata, open a GitHub pull request with the change.'));
        }

        if ($submission->questionnaire) {
            // Questionnaire was already filled, redirect o edit
            return $this->redirect(['action' => 'edit', $submission->questionnaire->id]);
        }

        $questionnaire = $this->Questionnaires->newEmptyEntity();

        if ($this->request->is('post')) {
            $questionnaire = $this->Questionnaires->patchEntity($questionnaire, $this->request->getData());

            $questionnaire->submission_id = $submission->id;

            if ($this->Questionnaires->save($questionnaire)) {
                $this->Flash->success(__('The questionnaire has been saved.'));

                return $this->redirect(['controller' => 'submissions', 'action' => 'confirmation', $submission->id]);
            }

            $this->Flash->error(__('The questionnaire could not be saved. Please, try again.'));
        }

        $scores = $this->Questionnaires->ReproducibilityScores->find('list');

        $this->set(compact('questionnaire', 'submission', 'scores'));
    }

    /**
     * Edit method
     *
     * @param null $submission_id Questionnaire id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($submission_id = null)
    {
        $userID = $this->getRequest()->getAttribute('identity')['id'] ?? null;

        $submission = $this->Questionnaires->Submissions->get($submission_id, [
            'contain' => [
                'Releases',
                'Listings',
                'Questionnaires',
            ],
            'conditions' => [
                'Submissions.id' => $submission_id,
                'Submissions.user_id' => $userID,
            ],
        ]);

        $questionnaire = $this->Questionnaires->get($submission->questionnaire->id);

        // Only allow submissions that are 'new' to be modified. Once released, they should follow the GitHub PR flow.
        if (date('Y-m-d') > $submission->release->release_date->i18nFormat('yyyy-MM-dd')) {
            $this->Flash->error(__('This submission was already released in a list. To modify its metadata, open a GitHub pull request with the change.'));
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $questionnaire = $this->Questionnaires->patchEntity($questionnaire, $this->request->getData());

            if ($this->Questionnaires->save($questionnaire)) {
                $this->Flash->success(__('The questionnaire has been saved.'));

                return $this->redirect(['controller' => 'submissions', 'action' => 'confirmation', $submission->id]);
            }

            $this->Flash->error(__('The questionnaire could not be saved. Please, try again.'));
        }

        $scores = $this->Questionnaires->ReproducibilityScores->find('list');

        $this->set(compact('questionnaire', 'submission', 'scores'));
    }

    /**
     * View method
     *
     * @param null $submission_id Questionnaire id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($submission_id = null)
    {
        $submission = $this->Questionnaires->Submissions->get($submission_id, [
            'contain' => [
                'Releases',
                'Listings',
                'Questionnaires',
            ],
            'conditions' => [
                'Submissions.id' => $submission_id,
            ],
        ]);

        $questionnaire = $this->Questionnaires->get($submission->questionnaire->id);

        // Only allow submissions that are 'new' to be modified. Once released, they should follow the GitHub PR flow.
        if (date('Y-m-d') > $submission->release->release_date->i18nFormat('yyyy-MM-dd')) {
            $this->Flash->error(__('This submission was already released in a list. To modify its metadata, open a GitHub pull request with the change.'));
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $questionnaire = $this->Questionnaires->patchEntity($questionnaire, $this->request->getData());

            if ($this->Questionnaires->save($questionnaire)) {
                $this->Flash->success(__('The questionnaire has been saved.'));

                return $this->redirect(['controller' => 'submissions', 'action' => 'confirmation', $submission->id]);
            }

            $this->Flash->error(__('The questionnaire could not be saved. Please, try again.'));
        }

        $scores = $this->Questionnaires->ReproducibilityScores->find('list');

        $this->set(compact('questionnaire', 'submission', 'scores'));
    }

    /**
     * Sample method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function sample()
    {
        $questionnaire = $this->Questionnaires->newEmptyEntity();

        $scores = $this->Questionnaires->ReproducibilityScores->find('list');

        $this->set(compact('questionnaire', 'scores'));
    }
}
