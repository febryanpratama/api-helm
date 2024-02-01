<?php

namespace App\Transformers;

use App\Story;
use League\Fractal\TransformerAbstract;

class StoryTransformer extends TransformerAbstract
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
    public function transform(Story $story)
    {
        return [
            'id' => $story->ID,
            'people_id' => $story->IDUser,
            'user_name' => $story->people->Name,
            'caption' => $story->caption,
            'hastag' => $story->hastag,
            'media' => fractal()
                ->collection($story->storyMedias)
                ->transformWith(new StoryMediaTransformer)->toArray(),
        ];
    }
}
