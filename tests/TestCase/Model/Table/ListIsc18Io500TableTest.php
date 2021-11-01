<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ListIsc18Io500Table;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ListIsc18Io500Table Test Case
 */
class ListIsc18Io500TableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ListIsc18Io500Table
     */
    protected $ListIsc18Io500;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.ListIsc18Io500',
        'app.Listings',
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
        $config = $this->getTableLocator()->exists('ListIsc18Io500') ? [] : ['className' => ListIsc18Io500Table::class];
        $this->ListIsc18Io500 = $this->getTableLocator()->get('ListIsc18Io500', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ListIsc18Io500);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ListIsc18Io500Table::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\ListIsc18Io500Table::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
