<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;

class SearchReindexCommand extends Command
{
    protected $signature = 'search:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes all articles to Elasticsearch';

    private $elasticsearch;

    public function handle()
    {
        $this->elasticsearch = app('elasticsearch');
        $this->info('Indexing all posts. This might take a while...');

        foreach (Post::cursor() as $post)
        {
            $this->elasticsearch->index([
                'index' => $post->getSearchIndex(),
//                'type' => $post->getSearchType(),
                'id' => $post->getKey(),
                'body' => $post->toSearchArray(),
            ]);

            // PHPUnit-style feedback
            $this->output->write('.');
        }

        foreach(User::get() as $user)
        {
            $this->elasticsearch->index([
                'index' => $user->getSearchIndex(),
                'id' => $user->uuid,
                'body' => $user->toSearchArray(),
            ]);

            // PHPUnit-style feedback
            $this->output->write('.');
        }

        $this->info("\nDone!");
    }
}
