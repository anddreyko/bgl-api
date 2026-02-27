<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Clients\Bgg;

use Bgl\Infrastructure\Clients\Bgg\XmlFieldExtractor;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Infrastructure\Clients\Bgg\XmlFieldExtractor
 */
#[Group('infrastructure', 'bgg', 'xml-extractor')]
final class XmlFieldExtractorCest
{
    private XmlFieldExtractor $extractor;

    public function _before(): void
    {
        $this->extractor = new XmlFieldExtractor();
    }

    public function testExtractsAttributeFromCurrentElement(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/><yearpublished value="1995"/></item>');

        $result = $this->extractor->extract($xml, ['@id' => 'bggId']);

        $i->assertSame(['bggId' => 13], $result);
    }

    public function testExtractsAttributeFromChildElement(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/><yearpublished value="1995"/></item>');

        $result = $this->extractor->extract($xml, ['name@value' => 'name']);

        $i->assertSame(['name' => 'Catan'], $result);
    }

    public function testReturnsNullForMissingChildElement(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/></item>');

        $result = $this->extractor->extract($xml, ['description@value' => 'description']);

        $i->assertSame(['description' => null], $result);
    }

    public function testReturnsNullWhenRequiredFieldIsMissing(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/></item>');

        $result = $this->extractor->extract(
            $xml,
            ['description@value' => 'description'],
            ['description'],
        );

        $i->assertNull($result);
    }

    public function testReturnsNullWhenRequiredFieldIsEmptyString(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value=""/></item>');

        $result = $this->extractor->extract(
            $xml,
            ['name@value' => 'name'],
            ['name'],
        );

        $i->assertNull($result);
    }

    public function testCastsNumericStringToInt(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="123"/>');

        $result = $this->extractor->extract($xml, ['@id' => 'bggId']);

        $i->assertSame(['bggId' => 123], $result);
    }

    public function testKeepsNonNumericStringAsString(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/></item>');

        $result = $this->extractor->extract($xml, ['name@value' => 'name']);

        $i->assertSame(['name' => 'Catan'], $result);
    }

    public function testFullExtractionMatchingBggSearchConfig(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/><yearpublished value="1995"/></item>');

        $mapping = [
            '@id' => 'bggId',
            'name@value' => 'name',
            'yearpublished@value' => 'yearPublished',
        ];

        $result = $this->extractor->extract($xml, $mapping, ['bggId', 'name']);

        $i->assertSame(
            [
                'bggId' => 13,
                'name' => 'Catan',
                'yearPublished' => 1995,
            ],
            $result,
        );
    }
}
