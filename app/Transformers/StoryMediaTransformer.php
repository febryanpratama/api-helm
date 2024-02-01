<?php

namespace App\Transformers;

use App\StoryMedia;
use League\Fractal\TransformerAbstract;

class StoryMediaTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(StoryMedia $media)
    {
        return [
            'id' => $media->ID,
            'story_Id' => $media->IDStorie,
            'location' => $media->Location,
            'thumblocation' => $media->ThumbLocation,
            'typemedia' => $media->Type,
            'size' => $media->Size,
        ];
    }
}
