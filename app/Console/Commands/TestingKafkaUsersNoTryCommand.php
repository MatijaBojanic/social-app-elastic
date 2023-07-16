<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

class TestingKafkaUsersNoTryCommand extends Command
{
    protected $signature = 'consume:users-no-try';

    protected $description = 'Command description';

    public function handle()
    {
        echo(config('kafka.brokers'));
        echo("\n");

        $consumer = Kafka::createConsumer()->withHandler(function($message) {
            dd($message);
        })->subscribe('users')->build();

        while (true) {
            $consumer->consume();
            sleep(1);
        }
    }
}
