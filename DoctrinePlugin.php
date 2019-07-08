<?php declare(strict_types=1);

namespace SunlightExtend\Doctrine;

use Sunlight\Plugin\ExtendPlugin;
use SunlightExtend\Doctrine\Action\ConsoleAction;

class DoctrinePlugin extends ExtendPlugin
{
    protected function getCustomActionList()
    {
        return ['console' => _lang('doctrine.console')];
    }

    function getAction($name)
    {
        if ($name === 'console') {
            return new ConsoleAction($this);
        }

        return parent::getAction($name);
    }
}
