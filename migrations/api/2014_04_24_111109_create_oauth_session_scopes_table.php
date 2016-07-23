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
 * This is the create oauth session scopes table migration class.
 *
 * @author Luca Degasperi <packages@lucadegasperi.com>
 */
class CreateOauthSessionScopesTable extends CmsMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefixCmsTable('oauth_session_scopes'), function (Blueprint $table) {
            $table->increments('id');
            $table->integer('session_id')->unsigned();
            $table->string('scope_id', 40);

            $table->nullableTimestamps();

            $table->index('session_id');
            $table->index('scope_id');

            $table->foreign('session_id', $this->prefixCmsTable('oauth_session_scopes_session_id_foreign'))
                  ->references('id')->on($this->prefixCmsTable('oauth_sessions'))
                  ->onDelete('cascade');

            $table->foreign('scope_id', $this->prefixCmsTable('oauth_session_scopes_scope_id_foreign'))
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
        Schema::table($this->prefixCmsTable('oauth_session_scopes'), function (Blueprint $table) {
            $table->dropForeign($this->prefixCmsTable('oauth_session_scopes_session_id_foreign'));
            $table->dropForeign($this->prefixCmsTable('oauth_session_scopes_scope_id_foreign'));
        });
        Schema::drop($this->prefixCmsTable('oauth_session_scopes'));
    }
}
