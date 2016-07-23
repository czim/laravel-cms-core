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
 * This is the create oauth refresh tokens table migration class.
 *
 * @author Luca Degasperi <packages@lucadegasperi.com>
 */
class CreateOauthRefreshTokensTable extends CmsMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefixCmsTable('oauth_refresh_tokens'), function (Blueprint $table) {
            $table->string('id', 40)->unique();
            $table->string('access_token_id', 40)->primary();
            $table->integer('expire_time');

            $table->nullableTimestamps();

            $table->foreign('access_token_id', $this->prefixCmsTable('oauth_refresh_tokens_access_token_id_foreign'))
                  ->references('id')->on($this->prefixCmsTable('oauth_access_tokens'))
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
        Schema::table($this->prefixCmsTable('oauth_refresh_tokens'), function (Blueprint $table) {
            $table->dropForeign($this->prefixCmsTable('oauth_refresh_tokens_access_token_id_foreign'));
        });

        Schema::drop($this->prefixCmsTable('oauth_refresh_tokens'));
    }
}
