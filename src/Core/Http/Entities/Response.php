<?php

declare(strict_types=1);

namespace App\Core\Http\Entities;

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

    public function __construct(mixed $data, bool $result = true)
    {
        $this->result = $result;
        $this->data = $data;
    }
}
