<?php

declare(strict_types=1);

namespace App\Http\Entities;

/**
 * @OA\Schema()
 */
class Response
{
    /**
     * The content.
     *
     * @var string
     * @OA\Property()
     */
    public string $data;

    /**
     * The status of response.
     *
     * @var bool
     * @OA\Property()
     */
    public bool $result = true;

    public function __construct(string $data, bool $result = true)
    {
        $this->result = $result;
        $this->data = $data;
    }
}
