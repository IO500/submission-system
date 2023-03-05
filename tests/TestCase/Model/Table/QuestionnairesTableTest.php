<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\QuestionnairesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\QuestionnairesTable Test Case
 */
class QuestionnairesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\QuestionnairesTable
     */
    protected $Questionnaires;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Questionnaires',
        'app.Submissions',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Questionnaires') ? [] : ['className' => QuestionnairesTable::class];
        $this->Questionnaires = $this->getTableLocator()->get('Questionnaires', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Questionnaires);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\QuestionnairesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\QuestionnairesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
