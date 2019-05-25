<?php

use Notion\Entities\Identifier;

class IdentifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideUrls
     */
    public function testCanCreateIdentifierFromUrl(string $url, string $expected)
    {
        $identifier = Identifier::fromUrl($url);
        $this->assertEquals($expected, $identifier->toString());
    }

    public function provideUrls()
    {
        return [
            [
                'https://www.notion.so/anahkiasen/Life-693febf12aa74a6283ea2cdd3ec50939',
                '693febf1-2aa7-4a62-83ea-2cdd3ec50939',
            ],
            [
                'https://www.notion.so/anahkiasen/3d13a98be599441485953749b4dbc8ad?v=f3c354c0549e4589adf10d7eff46a512',
                '3d13a98b-e599-4414-8595-3749b4dbc8ad',
            ],
        ];
    }
}
