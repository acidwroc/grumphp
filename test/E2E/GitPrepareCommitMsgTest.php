<?php

namespace GrumPHPTest\E2E;

class GitPrepareCommitMsgTest extends AbstractE2ETestCase
{
    /**
     * @test
     */
    function it_can_prefix_jira_commit_message()
    {
        $this->initializeGitInRootDir();
        $this->checkoutGit($this->rootDir, 'AM-1234-test');
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }
}
