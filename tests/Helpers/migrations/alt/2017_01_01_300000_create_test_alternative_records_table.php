<?php

use Czim\CmsCore\Support\Database\CmsMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestAlternativeRecordsTable extends CmsMigration
{

    public function up()
    {
        Schema::create($this->prefixCmsTable('test_alternative_records'), function (Blueprint $table) {
            $table->string('id')->primary();
        });
    }

    public function down()
    {
        Schema::drop($this->prefixCmsTable('test_alternative_records'));
    }
}
