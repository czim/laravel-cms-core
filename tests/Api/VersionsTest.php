<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Api;

use Czim\CmsCore\Test\ApiTestCase;

class VersionsTest extends ApiTestCase
{

    /**
     * @test
     */
    function it_responds_with_versions_for_core_components()
    {
        $response = $this->call('get', 'cms-api/meta/versions');

        $response->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     '*' => [ 'id', 'name', 'version' ]
                 ]
             ])
            ->assertJsonFragment([ 'name' => 'core' ])
            ->assertJsonFragment([ 'name' => 'auth' ])
            ->assertJsonFragment([ 'name' => 'module-manager' ]);

        // see if each item is follows the semantic version format
        $response = $response->decodeResponseJson();

        foreach ($response['data'] as $version) {
            static::assertRegExp('#^\d+\.\d+\.\d+$#', $version['version']);
        }
    }

}
