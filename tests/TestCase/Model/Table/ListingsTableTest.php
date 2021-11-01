<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ListingsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ListingsTable Test Case
 */
class ListingsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ListingsTable
     */
    protected $Listings;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Listings',
        'app.Types',
        'app.Releases',
        'app.ListIsc18Io500',
        'app.ListIsc1910node',
        'app.ListIsc19Full',
        'app.ListIsc19Io500',
        'app.ListIsc2010node',
        'app.ListIsc20Full',
        'app.ListIsc20Historical',
        'app.ListIsc20Io500',
        'app.ListIsc2110node',
        'app.ListIsc21Full',
        'app.ListIsc21Historical',
        'app.ListIsc21Io500',
        'app.ListSc17Io500',
        'app.ListSc1810node',
        'app.ListSc18Io500',
        'app.ListSc18Star10node',
        'app.ListSc18StarIo500',
        'app.ListSc1910node',
        'app.ListSc19Full',
        'app.ListSc19Historical',
        'app.ListSc19Io500',
        'app.ListSc19Scc',
        'app.ListSc2010node',
        'app.ListSc20Full',
        'app.ListSc20Historical',
        'app.ListSc20Io500',
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
        $config = $this->getTableLocator()->exists('Listings') ? [] : ['className' => ListingsTable::class];
        $this->Listings = $this->getTableLocator()->get('Listings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Listings);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ListingsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\ListingsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
