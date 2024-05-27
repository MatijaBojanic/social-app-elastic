<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function (Post $post) {
            $post->addToIndex();
        });

        static::updated(function (Post $post) {
            $post->addToIndex();
        });
    }

    public function addToIndex()
    {
        $elasticsearch = app('elasticsearch');

        $elasticsearch->index([
            'index' => 'posts',
            'id' => $this->id,
            'body' => $this->toSearchArray()
        ]);
    }

    public function getSearchIndex()
    {
        return 'posts';
    }

    public function toSearchArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'user_id' => $this->user_id
        ];
    }

    public static function search($query = '')
    {
        $elasticsearch = app('elasticsearch');
        $items = $elasticsearch->search([
            'index' => 'posts',
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['title^5', 'body'],
                        'fuzziness' => 'AUTO',
                    ],
                ],
            ],
        ]);

        return collect($items['hits']['hits'])->pluck('_source')->toArray();
    }
}
