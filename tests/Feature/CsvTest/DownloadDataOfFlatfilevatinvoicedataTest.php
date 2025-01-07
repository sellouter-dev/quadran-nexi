<?php

namespace Tests\Feature\Vendor;

use Mockery;
use Tests\TestCase;
use App\Services\CSVGeneratorService;


class DownloadDataOfFlatfilevatinvoicedataTest extends TestCase
{
    /**
     * Test fetching and processing vendor orde$vendorConnectorrs.
     *
     * @return void
     */
    public function testDownloadDataOfFlatfilevatinvoicedata()
    {
        $csvGeneratorService = new CSVGeneratorService();
        $csvGeneratorService->downloadDataOfCollections();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
