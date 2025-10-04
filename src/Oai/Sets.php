<?php

namespace Leconfe\OaiMetadata\Oai;

use App\Models\Conference;
use App\Models\Submission;
use App\Models\Topic;

use Leconfe\OaiMetadata\Oai\Wrapper\Error as OaiError;
use Leconfe\OaiMetadata\Oai\Query\ErrorCodes;

use Illuminate\Support\Str;

class Sets
{
    public const DELIMITER = ':';

    public static function parseSet(Conference $conference, string $set): Topic|string|null
    {
        $path = $conference->path;
        $getSet = Str::of($set);

        $listSets = $conference->topics->keyBy(function ($item) {
            return Str::of($item->name)->slug()->toString();
        });

        if ($getSet->startsWith($path)) {
            if ($getSet->endsWith($path)) {
                return $getSet->toString();
            } 

            $setSpec = $getSet->after(self::DELIMITER)->toString();
            return $listSets[$setSpec] ?? null;
        }

        return null;
    }

    public static function makeSet(Submission $paper): array
    {
        $prefix = $paper->load('conference')->conference->path;
        $result = [];

        $result[] = $prefix;

        foreach ($paper->load('topics')->topics as $topic) {
            $currentTopicSet = Str::of($topic->name)->slug();
            $delimiter = self::DELIMITER;
            $result[] = "{$prefix}{$delimiter}{$currentTopicSet}";
        }

        return $result;
    }

    public static function makeListSets(Conference $conference): OaiError|array
    {
        $listSets = [];
        $listSets[] = [
            'setSpec' => $conference->path,
            'setName' => $conference->name,
        ];

        foreach ($conference->topics as $topic) {
            $set = Str::of($topic->name)->slug();
            $delimiter = self::DELIMITER;
            $listSets[] = [
                'setSpec' => "{$conference->path}{$delimiter}{$set}",
                'setName' => $topic->name,
            ];
        }

        return count($listSets) > 0 
            ? $listSets
            : new OaiError(
                __('OaiMetadata::error.set.not-supported'),
                ErrorCodes::NO_SET_HIERARCHY
            );
    }
}