<?php

namespace Tests\Feature\Vendor;

use Mockery;
use Tests\TestCase;


class DownloadDataOfFlatfilevatinvoicedataTest extends TestCase
{
    /**
     * Test fetching and processing vendor orde$vendorConnectorrs.
     *
     * @return void
     */
    public function testDownloadDataOfFlatfilevatinvoicedata() {}

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
