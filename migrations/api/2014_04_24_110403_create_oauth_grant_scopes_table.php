<?php

/*
 * This file is part of OAuth 2.0 Laravel.
 *
 * (c) Luca Degasperi <packages@lucadegasperi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Czim\CmsCore\Support\Database\CmsMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This is the create oauth grant scopes table migration class.
 *
 * @author Luca Degasperi <packages@lucadegasperi.com>
 */
class CreateOauthGrantScopesTable extends CmsMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefixCmsTable('oauth_grant_scopes'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('grant_id', 40);
            $table->string('scope_id', 40);

            $table->nullableTimestamps();

            $table->index('grant_id');
            $table->index('scope_id');

            $table->foreign('grant_id', $this->prefixCmsTable('oauth_grant_scopes_grant_id_foreign'))
                ->references('id')->on($this->prefixCmsTable('oauth_grants'))
                ->onDelete('cascade');

            $table->foreign('scope_id', $this->prefixCmsTable('oauth_grant_scopes_scope_id_foreign'))
                ->references('id')->on($this->prefixCmsTable('oauth_scopes'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->prefixCmsTable('oauth_grant_scopes'), function (Blueprint $table) {
            $table->dropForeign($this->prefixCmsTable('oauth_grant_scopes_grant_id_foreign'));
            $table->dropForeign($this->prefixCmsTable('oauth_grant_scopes_scope_id_foreign'));
        });
        Schema::drop($this->prefixCmsTable('oauth_grant_scopes'));
    }
}
