<?php declare(strict_types=1);

namespace SunlightExtend\Doctrine\Action;

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Version;
use Sunlight\Action\ActionResult;
use Sunlight\Core;
use Sunlight\Plugin\Action\PluginAction;
use Sunlight\Util\Request;
use Sunlight\Xsrf;
use SunlightExtend\Doctrine\DoctrineBridge;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleAction extends PluginAction
{
    function getTitle()
    {
        return _lang('doctrine.console');
    }

    protected function execute()
    {
        $output = _buffer(function () { ?>
            <form method="post">
                <input type="text" name="input" class="inputbig cli-input">
                <input type="submit" value="<?php echo _lang('global.send') ?>" class="button">
                <?php echo Xsrf::getInput() ?>
            </form>
        <?php });

        $app = $this->getApplication();
        $appInput = new StringInput(Request::post('input', ''));
        $appOutput = new BufferedOutput();

        $e = null;
        try {
            $app->run($appInput, $appOutput);
        } catch (\Throwable $e) {
        }

        $appOutputString = $appOutput->fetch();
        if ($appOutputString !== '') {
            $output .= '<pre style="background-color: #000; color: #fff; padding: 10px;">' . _e($appOutputString) . '</pre>';
        }

        if ($e !== null) {
            $output .= Core::renderException($e);
        }

        return ActionResult::output($output);
    }

    private function getApplication(): Application
    {
        putenv('COLUMNS=160');
        putenv('LINES=1000');

        $app = new Application('Doctrine Command Line Interface', Version::VERSION);
        $app->setAutoExit(false);
        $app->setCatchExceptions(false);
        $app->setHelperSet(ConsoleRunner::createHelperSet(DoctrineBridge::getEntityManager()));

        ConsoleRunner::addCommands($app);

        return $app;
    }
}
