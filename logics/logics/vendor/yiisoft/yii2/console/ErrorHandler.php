<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\ErrorException;
use yii\base\UserException;
use yii\helpers\Console;

/**
 * ErrorHandler handles uncaught PHP errors and exceptions.
 *
 * ErrorHandler is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->errorHandler`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ErrorHandler extends \yii\base\ErrorHandler
{
    /**
     * Renders an exception using ansi format for console output.
     * @param \Exception $exception the exception to be rendered.
     */
    protected function renderException($exception)
    {
        $detail_message = "";
            if ($exception instanceof Exception) {
            $detail_message = $this->formatMessage("Exception ({$exception->getName()})");
            } elseif ($exception instanceof ErrorException) {
            $detail_message = $this->formatMessage($exception->getName());
            } else {
            $detail_message = $this->formatMessage('Exception');
            }
        $detail_message .= $this->formatMessage(" '" . get_class($exception) . "'", [Console::BOLD, Console::FG_BLUE])
                . " with message " . $this->formatMessage("'{$exception->getMessage()}'", [Console::BOLD]) //. "\n"
                . "\n\nin " . dirname($exception->getFile()) . DIRECTORY_SEPARATOR . $this->formatMessage(basename($exception->getFile()), [Console::BOLD])
                . ':' . $this->formatMessage($exception->getLine(), [Console::BOLD, Console::FG_YELLOW]) . "\n";
            if ($exception instanceof \yii\db\Exception && !empty($exception->errorInfo)) {
            $detail_message .= "\n" . $this->formatMessage("Error Info:\n", [Console::BOLD]) . print_r($exception->errorInfo, true);
        }
        $detail_message .= "\n" . $this->formatMessage("Stack trace:\n", [Console::BOLD]) . $exception->getTraceAsString();        

        if(YII_DEBUG)
        {
        if (PHP_SAPI === 'cli') {
                Console::stderr($detail_message . "\n");
        } else {
                echo $detail_message . "\n";
            }
        }
        else
        {
            try
            {
                \Yii::$app->mailer->compose()
                 ->setSubject("红色警戒：服务器端出现未知异常，请核对")
                 ->setTextBody($detail_message)
                 ->send();      
            }
            catch (\yii\base\Exception $e)
            {
            }
        }
    }

    /**
     * Colorizes a message for console output.
     * @param string $message the message to colorize.
     * @param array $format the message format.
     * @return string the colorized message.
     * @see Console::ansiFormat() for details on how to specify the message format.
     */
    protected function formatMessage($message, $format = [Console::FG_RED, Console::BOLD])
    {
        $stream = (PHP_SAPI === 'cli') ? \STDERR : \STDOUT;
        // try controller first to allow check for --color switch
        if (Yii::$app->controller instanceof \yii\console\Controller && Yii::$app->controller->isColorEnabled($stream)
            || Yii::$app instanceof \yii\console\Application && Console::streamSupportsAnsiColors($stream)) {
            $message = Console::ansiFormat($message, $format);
        }
        return $message;
    }
}