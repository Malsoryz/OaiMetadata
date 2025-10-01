<?php

namespace Leconfe\OaiMetadata\Isolated\Interface;

use App\Models\Submission;

interface Serializable
{
    public static function serialize(Submission $paper): static;
}