<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTracksTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('document_date')->nullable(); // Data doc
            $table->string('registration_date')->nullable(); // Data reg
            $table->string('document_number')->nullable(); // Numero doc
            $table->string('document_type')->nullable(); // Tipo documento
            $table->string('currency')->nullable(); // Divisa
            $table->float('gross_amount')->nullable(); // Importo lordo
            $table->float('net_amount')->nullable(); // Importo netto
            $table->float('vat_amount')->nullable(); // Importo IVA
            $table->string('split_payment')->nullable(); // Split payment
            $table->float('vat_code')->nullable(); // Codice iva
            $table->string('unique_code_rif3')->nullable(); // Codice univoco (chiave rif3)
            $table->string('buyer_tax_registration_type')->nullable(); // Tipo di registrazione (CF o VAT)
            $table->string('buyer_vat_number')->nullable(); // n. partita IVA cliente finale
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_tracks');
    }
}
