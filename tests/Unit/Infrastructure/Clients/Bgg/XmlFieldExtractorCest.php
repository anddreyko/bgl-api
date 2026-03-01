<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Clients\Bgg;

use Bgl\Core\Serialization\FieldMapping;
use Bgl\Core\Serialization\RequiredFields;
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

        $result = $this->extractor->extract($xml, FieldMapping::fromArray(['@id' => 'bggId']));

        $i->assertNotNull($result);
        $i->assertSame(['bggId' => 13], $result->toArray());
    }

    public function testExtractsAttributeFromChildElement(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/><yearpublished value="1995"/></item>');

        $result = $this->extractor->extract($xml, FieldMapping::fromArray(['name@value' => 'name']));

        $i->assertNotNull($result);
        $i->assertSame(['name' => 'Catan'], $result->toArray());
    }

    public function testReturnsNullForMissingChildElement(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/></item>');

        $result = $this->extractor->extract($xml, FieldMapping::fromArray(['description@value' => 'description']));

        $i->assertNotNull($result);
        $i->assertSame(['description' => null], $result->toArray());
    }

    public function testReturnsNullWhenRequiredFieldIsMissing(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/></item>');

        $result = $this->extractor->extract(
            $xml,
            FieldMapping::fromArray(['description@value' => 'description']),
            RequiredFields::fromArray(['description']),
        );

        $i->assertNull($result);
    }

    public function testReturnsNullWhenRequiredFieldIsEmptyString(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value=""/></item>');

        $result = $this->extractor->extract(
            $xml,
            FieldMapping::fromArray(['name@value' => 'name']),
            RequiredFields::fromArray(['name']),
        );

        $i->assertNull($result);
    }

    public function testCastsNumericStringToInt(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="123"/>');

        $result = $this->extractor->extract($xml, FieldMapping::fromArray(['@id' => 'bggId']));

        $i->assertNotNull($result);
        $i->assertSame(['bggId' => 123], $result->toArray());
    }

    public function testKeepsNonNumericStringAsString(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/></item>');

        $result = $this->extractor->extract($xml, FieldMapping::fromArray(['name@value' => 'name']));

        $i->assertNotNull($result);
        $i->assertSame(['name' => 'Catan'], $result->toArray());
    }

    public function testFullExtractionMatchingBggSearchConfig(UnitTester $i): void
    {
        $xml = new \SimpleXMLElement('<item id="13"><name value="Catan"/><yearpublished value="1995"/></item>');

        $mapping = FieldMapping::fromArray([
            '@id' => 'bggId',
            'name@value' => 'name',
            'yearpublished@value' => 'yearPublished',
        ]);

        $result = $this->extractor->extract($xml, $mapping, RequiredFields::fromArray(['bggId', 'name']));

        $i->assertNotNull($result);
        $i->assertSame(
            [
                'bggId' => 13,
                'name' => 'Catan',
                'yearPublished' => 1995,
            ],
            $result->toArray(),
        );
    }
}
