<?php

use Czim\CmsCore\Support\Database\CmsMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoreTestRecordsTable extends CmsMigration
{

    public function up()
    {
        Schema::create($this->prefixCmsTable('more_test_records'), function (Blueprint $table) {
            $table->string('id')->primary();
        });
    }

    public function down()
    {
        Schema::drop($this->prefixCmsTable('more_test_records'));
    }
}
