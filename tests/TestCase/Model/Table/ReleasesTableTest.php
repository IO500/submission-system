<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ReleasesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ReleasesTable Test Case
 */
class ReleasesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ReleasesTable
     */
    protected $Releases;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Releases',
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
        $config = $this->getTableLocator()->exists('Releases') ? [] : ['className' => ReleasesTable::class];
        $this->Releases = $this->getTableLocator()->get('Releases', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Releases);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ReleasesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\ReleasesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
