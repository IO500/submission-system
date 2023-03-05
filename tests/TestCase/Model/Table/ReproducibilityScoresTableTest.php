<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ReproducibilityScoresTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ReproducibilityScoresTable Test Case
 */
class ReproducibilityScoresTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ReproducibilityScoresTable
     */
    protected $ReproducibilityScores;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.ReproducibilityScores',
        'app.Questionnaires',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ReproducibilityScores') ? [] : ['className' => ReproducibilityScoresTable::class];
        $this->ReproducibilityScores = $this->getTableLocator()->get('ReproducibilityScores', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ReproducibilityScores);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ReproducibilityScoresTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\ReproducibilityScoresTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
