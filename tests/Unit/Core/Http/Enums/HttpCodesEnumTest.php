<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Http\Enums;

use App\Core\Http\Enums\HttpCodesEnum;
use Codeception\Test\Unit;

/**
 * @covers \App\Core\Http\Enums\HttpCodesEnum
 */
class HttpCodesEnumTest extends Unit
{
    /**
     * @param int $expect
     * @param HttpCodesEnum $codesEnum
     *
     * @dataProvider provideCode
     */
    public function testCode(HttpCodesEnum $codesEnum, int $expect): void
    {
        $this->assertEquals($expect, $codesEnum->value);
    }

    public function provideCode(): array
    {
        return [
            'success' => [HttpCodesEnum::Success, 200],
            'bad request' => [HttpCodesEnum::BadRequest, 400],
            'unauthorized' => [HttpCodesEnum::Unauthorized, 401],
            'forbidden' => [HttpCodesEnum::Forbidden, 403],
            'not found' => [HttpCodesEnum::NotFound, 404],
            'method not allowed' => [HttpCodesEnum::MethodNotAllowed, 405],
            'conflict' => [HttpCodesEnum::Conflict, 409],
            'gone' => [HttpCodesEnum::Gone, 410],
            'unprocessable entity' => [HttpCodesEnum::UnprocessableEntity, 422],
            'unexpected error' => [HttpCodesEnum::InternalServerError, 500],
            'not implemented' => [HttpCodesEnum::NotImplemented, 501],
        ];
    }

    /**
     * @param string $expect
     * @param HttpCodesEnum $codesEnum
     *
     * @dataProvider provideLabels
     */
    public function testLabel(HttpCodesEnum $codesEnum, string $expect): void
    {
        $this->assertEquals($expect, $codesEnum->label());
    }

    public function provideLabels(): array
    {
        return [
            'success' => [HttpCodesEnum::Success, 'Success.'],
            'bad request' => [HttpCodesEnum::BadRequest, 'Bad request.'],
            'unauthorized' => [HttpCodesEnum::Unauthorized, 'Unauthorized.'],
            'forbidden' => [HttpCodesEnum::Forbidden, 'Forbidden.'],
            'not found' => [HttpCodesEnum::NotFound, 'Not found.'],
            'method not allowed' => [HttpCodesEnum::MethodNotAllowed, 'Method not allowed.'],
            'conflict' => [HttpCodesEnum::Conflict, 'Conflict.'],
            'gone' => [HttpCodesEnum::Gone, 'Gone.'],
            'unprocessable entity' => [HttpCodesEnum::UnprocessableEntity, 'Unprocessable entity.'],
            'unexpected error' => [HttpCodesEnum::InternalServerError, 'Unexpected error.'],
            'not implemented' => [HttpCodesEnum::NotImplemented, 'Not implemented.'],
        ];
    }
}
