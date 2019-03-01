<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Thread;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ThreadTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'owner', 'replies'
    ];

    protected $defaultIncludes = [
        'owner'
    ];

    /**
     * A Fractal transformer.
     *
     * @param \App\Models\Thread $thread
     * @return array
     */
    public function transform(Thread $thread)
    {
        $data = [
            'title' => (string) $thread->title,
            'body' => (string) $thread->body,
            'created_at' => $thread->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $thread->updated_at->format('Y-m-d H:i:s')
        ];

        if(app()->environment() === 'testing') $data['id'] = $thread->id;

        return $data;
    }

    public function includeOwner(Thread $thread)
    {
        return $this->item($thread->owner, new UserTransformer);
    }

    public function includeReplies(Thread $thread)
    {
        $limit = 25;
        if(request()->has('limit')) {
            $input = (int)request('limit');
            if($input < 101 && $input > 9){
                $limit = $input;
            }
        }
        $replies = $thread->replies()->paginate($limit);

        return $this->collection(
            $replies->getCollection(),
            new ReplyTransformer()
        )->setPaginator(new IlluminatePaginatorAdapter($replies));
    }
}
