<?php

    use acm\acm;
    use acm\Objects\Schema;
    use Longman\TelegramBot\Exception\TelegramException;

    require __DIR__ . '/vendor/autoload.php';
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'CoffeeHouse' . DIRECTORY_SEPARATOR . 'CoffeeHouse.php');
    include_once(__DIR__ . DIRECTORY_SEPARATOR . 'TelegramClientManager' . DIRECTORY_SEPARATOR . 'TelegramClientManager.php');

    if(class_exists('DeepAnalytics\DeepAnalytics') == false)
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . 'DeepAnalytics' . DIRECTORY_SEPARATOR . 'DeepAnalytics.php');
    }

    $acm = new acm(__DIR__, 'Lydia Telegram Bot');

    $TelegramSchema = new Schema();
    $TelegramSchema->setDefinition('BotName', '<BOT NAME HERE>');
    $TelegramSchema->setDefinition('BotToken', '<BOT TOKEN>');
    $TelegramSchema->setDefinition('BotEnabled', 'true');
    $TelegramSchema->setDefinition('WebHook', 'http://localhost');
    $TelegramSchema->setDefinition('MaxConnections', 100);
    $acm->defineSchema('TelegramService', $TelegramSchema);

    $TelegramServiceConfiguration = $acm->getConfiguration('TelegramService');
    define("TELEGRAM_BOT_NAME", $TelegramServiceConfiguration['BotName'], false);

    if(strtolower($TelegramServiceConfiguration['BotName']) == 'true')
    {
        define("TELEGRAM_BOT_ENABLED", true);
    }
    else
    {
        define("TELEGRAM_BOT_ENABLED", false);
    }

    // Update Webhook
    $set_webhook = false;
    if(isset($_GET['set_webhook']))
    {
        if($_GET['set_webhook'] == '1')
        {
            $set_webhook = true;
        }
    }

    try
    {
        $telegram = new Longman\TelegramBot\Telegram(
            $TelegramServiceConfiguration['BotToken'],
            $TelegramServiceConfiguration['BotName']
        );
        $telegram->addCommandsPaths([__DIR__ . DIRECTORY_SEPARATOR . 'commands']);
    }
    catch (Longman\TelegramBot\Exception\TelegramException $e)
    {
        ?>
        <h1>Error</h1>
        <p>Something went wrong here, try again later</p>
        <?php
    }


    if($set_webhook == true)
    {
        try
        {
            $result = $telegram->setWebhook($TelegramServiceConfiguration['WebHook'], array(
                    'max_connections' => 100,
                    'allowed_updates' => []
                )
            );
            if ($result->isOk())
            {
                echo $result->getDescription();
            }
        }
        catch (Longman\TelegramBot\Exception\TelegramException $e)
        {
            ?>
            <h1>Error</h1>
            <p>Something went wrong here, try again later</p>
            <?php
        }
    }
    else
    {
        try
        {
            $telegram->handle();
        }
        catch (TelegramException $e)
        {
            ?>
            <h1>Access Denied</h1>
            <p>Nothing to see here, the current time is <?PHP print(hash('sha256', time() . 'IV')); ?></p>
            <?php
        }
    }