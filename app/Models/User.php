<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function (User $post) {
            $post->addToIndex();
        });

        static::updated(function (User $post) {
            $post->addToIndex();
        });
    }

    public function addToIndex()
    {
        $elasticsearch = app('elasticsearch');

        $elasticsearch->index([
            'index' => 'users',
            'id' => $this->id,
            'body' => $this->toSearchArray()
        ]);
    }

    public function getSearchIndex()
    {
        return 'users';
    }

    public function toSearchArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->name,
            'body' => $this->email,
            'user_id' => $this->created_at
        ];
    }

    public static function search($query = '')
    {
        $elasticsearch = app('elasticsearch');
        $items = $elasticsearch->search([
            'index' => 'users',
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['title^5', 'name'],
                        'fuzziness' => 'AUTO',
                    ],
                ],
            ],
        ]);

        return collect($items['hits']['hits'])->pluck('_source')->toArray();
    }
}
