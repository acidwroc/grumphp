<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command runs the git prepare-commit-msg hook.
 */
class PrepareCommitMsgCommand extends Command
{
    const COMMAND_NAME = 'git:prepare-commit-msg';

    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    public function __construct(
        GrumPHP $config
    ) {
        parent::__construct();

        $this->grumPHP = $config;
    }

    public static function getDefaultName(): string
    {
        return self::COMMAND_NAME;
    }

    /**
     * Configure command.
     */
    protected function configure(): void
    {
        $this->setDescription('Executed by the prepare-commit-msg commit hook');
    }

    /**
     * @return int|void
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=yellow>GrumPHP detected a prepare-commit-msg command.</fg=yellow>');
    }
}
