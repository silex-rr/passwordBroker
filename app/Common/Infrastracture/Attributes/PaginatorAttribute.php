<?php

namespace App\Common\Infrastracture\Attributes;

use Illuminate\Pagination\Paginator;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema(
    schema: "Common_Paginator",
    properties: [
        new Property(property: "data"),
        new Property(property: "current_page", type: "integer", minimum: 1),
        new Property(property: "last_page", type: "integer", minimum: 1),
        new Property(property: "per_page", type: "integer", minimum: 1),
        new Property(property: "total", type: "integer", minimum: 0),
    ],
    type: "object"
)]
class PaginatorAttribute extends Paginator
{

}
