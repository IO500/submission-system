<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\SubmissionsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\SubmissionsController Test Case
 *
 * @uses \App\Controller\SubmissionsController
 */
class SubmissionsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Submissions',
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
        'app.Listings',
        'app.ListingsSubmissions',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\SubmissionsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\SubmissionsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\SubmissionsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\SubmissionsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\SubmissionsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
