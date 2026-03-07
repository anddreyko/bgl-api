<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api\V1\Responses;

use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\TotalCount;
use Bgl\Presentation\Api\HttpCode;

final readonly class SuccessResponse
{
    public int $code;

    /**
     * @param mixed $data Response payload (single object or array)
     */
    public function __construct(
        public mixed $data,
        public HttpCode $httpCode = HttpCode::Ok,
        public ?PageNumber $page = null,
        public ?PageSize $limit = null,
        public ?TotalCount $total = null,
    ) {
        $this->code = 0;
    }
}
