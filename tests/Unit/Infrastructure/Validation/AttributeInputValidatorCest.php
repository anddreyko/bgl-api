<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Infrastructure\Validation;

use Bgl\Core\Validation\Attributes\MinLength;
use Bgl\Core\Validation\Attributes\NotBlank;
use Bgl\Core\Validation\Attributes\ValidEmail;
use Bgl\Core\Validation\Attributes\ValidUuid;
use Bgl\Infrastructure\Validation\AttributeInputValidator;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Infrastructure\Validation\AttributeInputValidator
 */
#[Group('infrastructure', 'validation')]
final class AttributeInputValidatorCest
{
    private AttributeInputValidator $validator;

    public function _before(): void
    {
        $this->validator = new AttributeInputValidator();
    }

    public function testValidObjectPasses(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: 'user@example.com',
            name: 'John Doe',
            password: 'securepassword',
        );

        $result = $this->validator->validate($dto);

        $i->assertFalse($result->hasErrors());
    }

    public function testNotBlankFailsOnEmptyString(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: 'user@example.com',
            name: '',
            password: 'securepassword',
        );

        $result = $this->validator->validate($dto);

        $i->assertTrue($result->hasErrors());
        $i->assertTrue($result->getErrors()->hasField('name'));
    }

    public function testNotBlankFailsOnWhitespaceOnly(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: 'user@example.com',
            name: '   ',
            password: 'securepassword',
        );

        $result = $this->validator->validate($dto);

        $i->assertTrue($result->hasErrors());
        $i->assertTrue($result->getErrors()->hasField('name'));
    }

    public function testValidEmailFailsOnInvalidEmail(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: 'not-an-email',
            name: 'John',
            password: 'securepassword',
        );

        $result = $this->validator->validate($dto);

        $i->assertTrue($result->hasErrors());
        $i->assertTrue($result->getErrors()->hasField('email'));
    }

    public function testMinLengthFailsOnShortValue(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: 'user@example.com',
            name: 'John',
            password: 'short',
        );

        $result = $this->validator->validate($dto);

        $i->assertTrue($result->hasErrors());
        $i->assertTrue($result->getErrors()->hasField('password'));
    }

    public function testValidUuidFailsOnInvalidUuid(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: 'not-a-uuid',
            email: 'user@example.com',
            name: 'John',
            password: 'securepassword',
        );

        $result = $this->validator->validate($dto);

        $i->assertTrue($result->hasErrors());
        $i->assertTrue($result->getErrors()->hasField('id'));
    }

    public function testMultipleErrorsAccumulate(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: 'bad-id',
            email: 'bad-email',
            name: '',
            password: 'short',
        );

        $result = $this->validator->validate($dto);

        $i->assertTrue($result->hasErrors());
        $errors = $result->getErrors();
        $i->assertTrue($errors->hasField('id'));
        $i->assertTrue($errors->hasField('email'));
        $i->assertTrue($errors->hasField('name'));
        $i->assertTrue($errors->hasField('password'));
    }

    public function testObjectWithoutConstructorPasses(UnitTester $i): void
    {
        $dto = new NoConstructorDto();

        $result = $this->validator->validate($dto);

        $i->assertFalse($result->hasErrors());
    }

    public function testNotBlankFailsOnNull(UnitTester $i): void
    {
        $dto = new NullableDto(name: null);

        $result = $this->validator->validate($dto);

        $i->assertTrue($result->hasErrors());
        $i->assertTrue($result->getErrors()->hasField('name'));
    }

    public function testMinLengthExactBoundary(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: '550e8400-e29b-41d4-a716-446655440000',
            email: 'user@example.com',
            name: 'John',
            password: '12345678',
        );

        $result = $this->validator->validate($dto);

        $i->assertFalse($result->hasErrors());
    }

    public function testValidUuidAcceptsUppercase(UnitTester $i): void
    {
        $dto = new ValidDto(
            id: '550E8400-E29B-41D4-A716-446655440000',
            email: 'user@example.com',
            name: 'John',
            password: 'securepassword',
        );

        $result = $this->validator->validate($dto);

        $i->assertFalse($result->hasErrors());
    }
}

final readonly class ValidDto
{
    public function __construct(
        #[ValidUuid]
        public string $id,
        #[NotBlank]
        #[ValidEmail]
        public string $email,
        #[NotBlank]
        public string $name,
        #[NotBlank]
        #[MinLength(min: 8)]
        public string $password,
    ) {
    }
}

final class NoConstructorDto
{
}

final readonly class NullableDto
{
    public function __construct(
        #[NotBlank]
        public ?string $name,
    ) {
    }
}
