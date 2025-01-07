<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Jobs\FetchSellerInventory;
use App\Models\CustomerCredential;

class GetInventoryReportTest extends TestCase
{
    /**
     * Test fetching and processing vendor orde$vendorConnectorrs.
     *
     * @return void
     */
    public function testFetchInventoryReports()
    {
        $credential = CustomerCredential::first();

        $reportJob = new FetchSellerInventory($credential);

        $this->assertNull($reportJob->handle());

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
