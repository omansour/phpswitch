<?php
namespace jubianchi\PhpSwitch\Console\Command\PHP;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use jubianchi\PhpSwitch\Console\Command\Command;

class SwitchCommand extends Command
{
    const NAME = 'php:switch';
    const DESC = 'Switch PHP version';

    /**
     * @param string $name
     */
    public function __construct($name = self::NAME)
    {
        parent::__construct($name);

        $this->addArgument('version', InputArgument::REQUIRED, 'Switch PHP version (alias-x.y.z)');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        $version = ('off' === $version ? null : $version);

        if (null !== $version) {
            $path = $this->getApplication()->getService('app.workspace.installed.path');
            $finder = new Finder();
            $finder
                ->in($path)
                ->directories()
                ->name('*-*')
                ->depth(0)
            ;

            if (0 === count($finder)) {
                throw new \InvalidArgumentException(sprintf('Version %s is not installed', $version));
            }
        }

        $this->getConfiguration()
            ->set('version', $version)
            ->dump()
        ;

        $this->log(
            sprintf('PHP switched to <info>%s</info>', $version ?: 'system default version'),
            \Monolog\Logger::INFO,
            $output
        );

        return 0;
    }
}
