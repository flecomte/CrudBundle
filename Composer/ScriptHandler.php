<?php

namespace FLE\Bundle\CrudBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Process\Process;

class ScriptHandler extends \Sensio\Bundle\DistributionBundle\Composer\ScriptHandler
{
    /**
     * @param Event  $event
     * @param string $cmd
     * @param null   $options
     */
    protected static function executeRawCommand (Event $event, $cmd, $options = null)
    {
        if ($options === null) {
            $options = self::getOptions($event);
        }
        $process = new Process($cmd, null, null, null, $options['process-timeout']);
        $process->run(function ($type, $buffer) use ($event) { $event->getIO()->write($buffer, false); });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf("An error occurred when executing the \"%s\" command:\n\n%s\n\n%s.", escapeshellarg($cmd), $process->getOutput(), $process->getErrorOutput()));
        }
    }

    /**
     * @param $event Event A instance
     */
    public static function schemaUpdate(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir = self::getConsoleDir($event, 'SQL Install');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'doctrine:schema:update --force', $options['process-timeout']);
    }

    /**
     * @param $event Event A instance
     */
    public static function bootstrapFontInstall(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir = self::getConsoleDir($event, 'Font Install');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'mopa:bootstrap:install:font', $options['process-timeout']);
    }

    /**
     * @param $event Event A instance
     */
    public static function bowerInstall(Event $event)
    {
        $options = self::getOptions($event);
        self::executeRawCommand($event, 'bower install -q', $options);
    }

    /**
     * @param $event Event A instance
     */
    public static function elasticaPopulate(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir = self::getConsoleDir($event, 'Elastica Populate');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'fos:elastica:populate', $options['process-timeout']);
    }

    /**
     * @param $event Event A instance
     */
    public static function asseticDump(Event $event)
    {
        $options = self::getOptions($event);
        $consoleDir = self::getConsoleDir($event, 'Elastica Populate');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'assetic:dump', $options['process-timeout']);
    }
}