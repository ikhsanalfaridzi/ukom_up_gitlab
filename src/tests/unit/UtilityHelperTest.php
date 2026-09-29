<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class UtilityHelperTest extends CIUnitTestCase
{
    public function testUuidV4FallsBackWhenRamseyUuidIsUnavailable()
    {
        require_once dirname(__DIR__, 2) . '/app/Helpers/utility_helper.php';

        $uuid = uuid_v4();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }
}
