<?php

namespace Leconfe\OaiMetadata\Oai;

use App\Models\Submission;
use App\Models\Topic;
use Illuminate\Support\Str;

class Sets
{
    public static function parseSet(Submission $paper, string $set): ?Topic
    {
        $result = null;

        foreach ($paper->topics as $topic) {
            if (dd($paper->conference->path.':'.Str::of($topic->name)->slug(), $set)) {
                $result = $topic;
            }
        }

        return $result;
    }
}