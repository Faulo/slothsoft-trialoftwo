<?php
namespace Slothsoft\ToT;

class UnityProject {

    const FILE_UNITY = '%s/%s/Editor/Unity.exe';

    const FILE_VERSION = '/ProjectSettings/ProjectVersion.txt';

    const FILE_PACKAGES = '/Packages/packages-lock.json';

    private $projectPath;

    private $unityVersion;

    private $unityPath;

    public $packages = [];

    public function __construct(string $unityPath, string $projectPath) {
        assert(is_dir($unityPath), "Path $unityPath not found");
        assert(is_dir($projectPath), "Path $projectPath not found");

        $this->unityPath = realpath($unityPath);
        $this->projectPath = realpath($projectPath);
        $this->loadProject();
    }

    private function loadProject() {
        $contents = $this->loadFile($this->projectPath . self::FILE_VERSION);
        $match = [];
        if (preg_match('~m_EditorVersion: (.+)~', $contents, $match)) {
            $this->unityVersion = trim($match[1]);

            $unityFile = sprintf(self::FILE_UNITY, $this->unityPath, $this->unityVersion);
            assert(is_file($unityFile), "File $unityFile not found");
            $this->unityFile = realpath($unityFile);
        }
        $tmp = json_decode($this->loadFile($this->projectPath . self::FILE_PACKAGES), true);
        if (is_array($tmp) and isset($tmp['dependencies'])) {
            $this->packages = $tmp['dependencies'];
        }
    }

    private function loadFile(string $path): string {
        assert(is_file($path), "Path $path not found");
        return file_get_contents($path);
    }

    public function execute(string $path, string $file, string $method): int {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
        assert(is_dir($path), "Path $path not found");
        $path = realpath($path);
        $target = $path . $file;

        $args = [];
        $args[] = escapeshellarg($this->unityFile);
        $args[] = escapeshellarg($this->projectPath);
        $args[] = escapeshellarg($method);
        $args[] = escapeshellarg($target);
        $command = vsprintf('%s -quit -accept-apiupdate -batchmode -nographics -projectPath %s -executeMethod %s %s', $args);

        return CLI::execute($command);
    }
}

