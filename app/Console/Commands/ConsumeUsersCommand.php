<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;

class ConsumeUsersCommand extends Command
{
    protected $signature = 'consume:users';

    protected $description = 'Command description';

    private const TOPIC_NAME = 'users';
    private const WAIT_TIME = 5;

    public function handle()
    {
        echo config('kafka.brokers') . PHP_EOL;

        while (true) {
            $consumer = $this->createConsumer();

            if ($this->tryConsume($consumer)) {
                break;
            }

            sleep(self::WAIT_TIME);
        }

        while (true) {
            if (!$this->tryConsume($consumer)) {
                $consumer = $this->createConsumer();
            }

            sleep(1);
        }
    }

    private function createConsumer()
    {
        return Kafka::createConsumer()->withHandler(function (KafkaConsumerMessage $message) {


            logger($message->getBody());

            User::create([
                'name' => $message->getBody()['name'],
                'email' => $message->getBody()['email'],
                'uuid' => $message->getBody()['uuid'],
                'username' => $message->getBody()['username']
            ]);

            logger("User created from kafka message");

        })->subscribe(self::TOPIC_NAME)->build();
    }

    private function tryConsume($consumer)
    {
        try {
            $consumer->consume();
            return true;
        } catch(\Exception $e) {
            if ($e->getMessage() !== "Broker: Unknown topic or partition") {
                logger($e->getMessage());
                throw $e;
            }

            logger('Topic does not exist, waiting for it to be created...');
            return false;
        }
    }
}
