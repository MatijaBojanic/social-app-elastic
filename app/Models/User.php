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
        'username',
        'uuid'
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
            'id' => $this->uuid,
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
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username
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
                        'fields' => ['username^5', 'name', 'email'],
                        'fuzziness' => 'AUTO',
                    ],
                ],
            ],
        ]);

        return collect($items['hits']['hits'])->pluck('_source')->toArray();
    }
}
