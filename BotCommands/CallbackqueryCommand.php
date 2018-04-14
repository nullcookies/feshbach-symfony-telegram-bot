<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Bot\Exceptions\UnrecognizedCommandException;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Request;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.1.1';

    /**
     * @var array
     */
    private $allowedCallbackCommands = [
        'pugBomb' => 'pugBomb',
        'personalSurvey' => 'personalSurvey'
    ];

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function execute()
    {
        $callback_query = $this->getCallbackQuery();
        $data = $callback_query->getData();

        $command = explode('__', $data);
        $callbackType = $command[0];
        $callbackName = $command[1];

        try {

            switch ($callbackType) {
                case 'command':
                    return $this->executeCommandByName($callbackName);
                default:
                    return $this->generateInvalidCommandReply($callback_query);
            }

        } catch (\Exception $e) {
            return $this->generateInvalidCommandReply($callback_query);
        }
    }

    /**
     * @param string $commandName
     * @return mixed
     * @throws \Longman\TelegramBot\Exception\TelegramException
     * @throws UnrecognizedCommandException
     */
    private function executeCommandByName(string $commandName)
    {
        if (!isset($this->allowedCallbackCommands[$commandName])
            || !$this->getTelegram()->getCommandObject($this->allowedCallbackCommands[$commandName])) {
            throw new UnrecognizedCommandException();
        }

        return $this->getTelegram()->executeCommand($this->allowedCallbackCommands[$commandName]);
    }

    /**
     * @param CallbackQuery $callback_query
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    private function generateInvalidCommandReply(CallbackQuery $callback_query)
    {
        $data = [];
        $data['callback_query_id'] = $callback_query->getId();
        $data['text'] = 'Invalid request!';
        $data['show_alert'] = true;
        return Request::answerCallbackQuery($data);
    }

}