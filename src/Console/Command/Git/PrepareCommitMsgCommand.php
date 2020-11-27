<?php

declare(strict_types=1);

namespace GrumPHP\Console\Command\Git;

use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command runs the git prepare-commit-msg hook.
 */
class PrepareCommitMsgCommand extends Command
{
    const COMMAND_NAME = 'git:prepare-commit-msg';

    const EXIT_CODE_OK = 0;

    private Paths $paths;

    private Filesystem $filesystem;

    private array $config;

    public function __construct(Paths $paths, Filesystem $filesystem, array $config)
    {
        parent::__construct();

        $this->paths = $paths;
        $this->filesystem = $filesystem;
        $this->config = $config;
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

        $this->addOption(
            'branch-name',
            null,
            InputOption::VALUE_REQUIRED,
            'Set name of the branch containing JIRA issue.'
        );

        $this->addArgument('commit-msg-file', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=yellow>GrumPHP detected a prepare-commit-msg command.</fg=yellow>');

        /** @var string $commitMsgPath */
        $commitMsgPath = $input->getArgument('commit-msg-file');

        /** @var string $branchName */
        $branchName = $input->getOption('branch-name');

        if (!$this->filesystem->isAbsolutePath($commitMsgPath)) {
            $commitMsgPath = $this->filesystem->buildPath($this->paths->getGitWorkingDir(), $commitMsgPath);
        }

        if ($issue = $this->getJiraIssue($branchName)) {
            $outputPath = $commitMsgPath.'.__tmp';

            $this->appendIssueToCommitMsg(
                $commitMsgPath,
                $outputPath,
                $issue
            );

            $this->overwriteCommitMsg($commitMsgPath, $outputPath);
        }

        return self::EXIT_CODE_OK;
    }

    private function getJiraIssue(string $branchName): ?string
    {
        $pattern = $this->config['pattern'];

        if (preg_match("/$pattern/", $branchName, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function appendIssueToCommitMsg(string $inputPath, string $outputPath, string $issue): void
    {
        chmod($inputPath, 0777);

        $input = fopen($inputPath, 'r');
        $output = fopen($outputPath, 'w+');
        $index = 0;

        while (($line = fgets($input)) !== false) {
            $toWrite = $line;

            if ($index === 0) {
                $toWrite = sprintf('%s - %s', $issue, $toWrite);
            }

            fwrite($output, $toWrite);
            $index++;
        }

        fclose($output);
        fclose($input);
    }

    private function overwriteCommitMsg(string $inputPath, string $outputPath): void
    {
        unlink($inputPath);
        rename($outputPath, $inputPath);
        chmod($inputPath, 0777);
    }
}
