<?php
namespace Slothsoft\ToT;

class GitProject {

    private $projectPath;

    public function __construct(string $projectPath) {
        assert(is_dir($projectPath), "Path $projectPath not found");

        $this->projectPath = realpath($projectPath);
    }

    public function add(string $flags = '.') {
        $this->execute("add $flags");
    }

    public function commit(string $message) {
        $this->execute(sprintf('commit -m %s', escapeshellarg($message)));
    }

    public function pull(string $flags = '-f') {
        $this->execute("pull $flags");
    }

    public function push(string $flags = '') {
        $this->execute("push $flags");
    }

    public function reset(string $flags = '--hard') {
        $this->execute("reset $flags");
    }

    public function clean(string $flags = '-d -f') {
        $this->execute("clean $flags");
    }

    public function execute($gitArgs) {
        $command = sprintf('git -C %s %s', escapeshellarg($this->projectPath), $gitArgs);
        CLI::execute($command);
    }
}

