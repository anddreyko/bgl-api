<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Entities;

/**
 * @OpenApi\Annotations\Schema()
 */
class Response
{
    /**
     * The content.
     *
     * @var mixed
     * @OpenApi\Annotations\Property()
     */
    public mixed $data;

    /**
     * The status of response.
     *
     * @var bool
     * @OpenApi\Annotations\Property()
     */
    public bool $result = true;

    /**
     * The internal code of error.
     *
     * @var int|null
     * @OpenApi\Annotations\Property()
     */
    public ?int $code = null;

    public function __construct(mixed $data, bool $result = true, ?int $code = null)
    {
        $this->result = $result;
        $this->data = $data;
        $this->code = $code;
    }
}
