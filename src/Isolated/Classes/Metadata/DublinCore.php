<?php

namespace Leconfe\OaiMetadata\Isolated\Classes\Metadata;

use App\Models\Submission;
use Leconfe\OaiMetadata\Isolated\Interface\Serializable;

class DublinCore implements Serializable
{
    public static function serialize(Submission $paper): static
    {
        return new static();
    }
}