<?php

use Czim\CmsCore\Support\Database\CmsMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestRecordsTable extends CmsMigration
{

    public function up()
    {
        Schema::create($this->prefixCmsTable('test_records'), function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('description');
            $table->nullableTimestamps();
        });
    }

    public function down()
    {
        Schema::drop($this->prefixCmsTable('test_records'));
    }
}
