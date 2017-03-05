<?php
namespace Czim\CmsCore\Test\Support\Data\Menu;

use Czim\CmsCore\Support\Data\Menu\PermissionsIndexData;
use Czim\CmsCore\Test\TestCase;

class PermissionIndexDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_stringified_base_encoded_identifier_for_nested_keys_array()
    {
        $data = new PermissionsIndexData();

        $stringified = base64_encode('test') . '.' . base64_encode(1) . '.' . base64_encode('deep');

        static::assertEquals($stringified, $data->stringifyNodeKey(['test', 1, 'deep']));
    }

    /**
     * @test
     */
    function it_returns_top_level_identifier_when_stringifying_empty_key_array()
    {
        $data = new PermissionsIndexData();

        static::assertEquals(PermissionsIndexData::KEY_TOP_LEVEL, $data->stringifyNodeKey([]));
    }

}
