<?php
namespace Slothsoft\ToT;

class MLContext {

    const ROOT_PATH = 'python';

    const LOCK_EXTENSION = '.lock';
	
	const ADDITIONAL_REPOSITORIES = '-f https://download.pytorch.org/whl/torch_stable.html';
    
    private $workDirectory;

    public $version;

    private $pythonPath;

    private $pythonLock;

    public function __construct(string $workDirectory, string $version) {
        assert(is_dir($workDirectory));
        $this->workDirectory = realpath($workDirectory);
        $this->version = $version;
        $this->pythonPath = $this->workDirectory . DIRECTORY_SEPARATOR . self::ROOT_PATH . DIRECTORY_SEPARATOR . $version;
        $this->pythonLock = $this->pythonPath . self::LOCK_EXTENSION;
    }
	
	public function lockExists() : bool {
		return is_file($this->pythonLock);
	}
	public function loadLock() {
        if ($this->lockExists()) {
            $this->install();
        } else {
            $this->update();
            $this->freeze();
        }
		assert($this->lockExists());
		$this->pythonLock = realpath($this->pythonLock);
	}
	
	public function pathExists() : bool {
		return is_dir($this->pythonPath);
	}
	public function loadPath() {
		if (!$this->pathExists()) {
			$this->setup();
		}
		assert($this->pathExists());
        $this->pythonPath = realpath($this->pythonPath);
	}

    public function setup(): int {
        return CLI::execute(sprintf('virtualenv %s', escapeshellarg($this->pythonPath)));
    }
	

    public function update(): int {
        return $this->executeIn($this->workDirectory, "pip install mlagents==$this->version.* tensorflow numpy~=1.18.0 --upgrade --upgrade-strategy eager");
    }

    public function execute(string $command): int {
        return $this->executeIn($this->workDirectory, $command);
    }

    public function freeze(): int {
        return $this->executeIn($this->workDirectory, sprintf('pip freeze > %s', escapeshellarg($this->pythonLock)));
    }

    public function install(): int {
        return $this->executeIn($this->workDirectory, sprintf('pip install --no-warn-script-location -r %s %s', escapeshellarg($this->pythonLock), self::ADDITIONAL_REPOSITORIES));
    }

    public function learn(string $workDirectory, string $arguments): int {
        return $this->executeIn($workDirectory, "mlagents-learn $arguments");
    }

    private function executeIn(string $workDirectory, string $command): int {
        $file = $this->pythonPath . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . $command;
        echo PHP_EOL . PHP_EOL . $file . PHP_EOL;
        
        $descriptorspec = [
            STDIN,
            STDOUT,
            STDOUT
        ];
        $pipes = [];
        $env = null;
        $options = [
            'bypass_shell' => false,
            'blocking_pipes' => true
        ];
        
        $process = proc_open($file, $descriptorspec, $pipes, $workDirectory, $env, $options);
        
        if (is_resource($process)) {
            $returnCode = proc_close($process);
        } else {
            $returnCode = - 1;
        }
        
        return $returnCode;
    }
}