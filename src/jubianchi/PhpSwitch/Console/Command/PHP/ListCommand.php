<?php
namespace jubianchi\PhpSwitch\Console\Command\PHP;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use jubianchi\PhpSwitch\Console\Command\Command;
use jubianchi\PhpSwitch\PHP;

class ListCommand extends Command
{
    const NAME = 'php:list';
    const DESC = 'Lists PHP versions';

    /**
     * @param string $name
     */
    public function __construct($name = self::NAME)
    {
        parent::__construct($name);

        $this
            ->addOption('installed', 'i', InputOption::VALUE_NONE, 'Version name alias')
            ->addOption('available', 'l', InputOption::VALUE_NONE, 'Version name alias')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Version name alias')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = ($input->getOption('all') || (false === $input->getOption('installed') && false === $input->getOption('available')));
        if ($all || $input->getOption('installed')) {
            $this->log($this->getHelper('formatter')->formatBlock('Installed versions', 'info'));
            $this->listInstalled($output);
        }

        if ($all || $input->getOption('available')) {
            $this->log($this->getHelper('formatter')->formatBlock('Available versions', 'info'));
            $this->listAvailable($output);
        }

        return 0;
    }

    protected function listAvailable(OutputInterface $output)
    {
        $finder = new PHP\Finder();

        foreach ($finder as  $version) {
            $this->log(
                sprintf(
                    '<info>%-15s</info> <comment>%s</comment>',
                    $version,
                    sprintf($version->getUrl(), 'a')
                ),
                \Monolog\Logger::INFO,
                $output
            );
        }
    }

    protected function listInstalled(OutputInterface $output)
    {
        $path = $this->getApplication()->getService('app.workspace.installed.path');
        $finder = new Finder();
        $finder
            ->in($path)
            ->directories()
            ->name('*-*')
            ->depth(0)
        ;

        $versions = array();
        foreach ($finder as $directory) {
            $versions[$directory->getRealPath()] = $directory->getRelativePathname();
        }

        uasort(
            $versions,
            function($a, $b) {
                $pattern = '/(5\.\d+\.\d+)$/';

                preg_match($pattern, $a, $matches);
                $a = $matches[1];
                preg_match($pattern, $b, $matches);
                $b = $matches[1];

                return version_compare($a, $b);
            }
        );

        foreach ($versions as $path => $version) {
            $this->log(
                sprintf(
                    '<info>%-15s</info> <comment>%s</comment>',
                    $version,
                    $path
                ),
                \Monolog\Logger::INFO,
                $output
            );

        }
    }
}
