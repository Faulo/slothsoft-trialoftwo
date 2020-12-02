<?php
namespace Slothsoft\ToT;

class CLI {

    public static function execute(string $command): int {
        echo PHP_EOL . PHP_EOL . '> ' . $command . PHP_EOL;
        $returnCode = 0;
        passthru($command, $returnCode);
        return $returnCode;
    }
}