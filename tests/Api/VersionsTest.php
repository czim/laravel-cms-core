<?php
namespace Czim\CmsCore\Test\Api;

use Czim\CmsCore\Test\ApiTestCase;

class VersionsTest extends ApiTestCase
{

    /**
     * @test
     */
    function it_responds_with_versions_for_core_components()
    {
        $this->call('get', 'cms-api/meta/versions');

        $this->seeStatusCode(200)
             ->seeJson()
             ->seeJsonStructure([
                 'data' => [
                     '*' => [ 'id', 'name', 'version' ]
                 ]
             ])
            ->seeJsonContains([ 'name' => 'core' ])
            ->seeJsonContains([ 'name' => 'auth' ])
            ->seeJsonContains([ 'name' => 'module-manager' ]);

        // see if each item is follows the semantic version format
        $response = $this->decodeResponseJson();

        foreach ($response['data'] as $version) {
            $this->assertRegExp('#^\d+\.\d+\.\d+$#', $version['version']);
        }
    }

}
