<?php

declare(strict_types=1);

namespace App\Service\Common;

use Safe\Exceptions\FilesystemException;
use Symfony\Component\Console\Style\SymfonyStyle;

class FileWriteService
{
    /**
     * @throws FilesystemException
     */
    public function writeToFile(
        string $filename,
        string $contents,
        bool $addToGit = true,
        ?SymfonyStyle $output = null
    ): void {
        if (is_file($filename)) {
            throw new \InvalidArgumentException(sprintf('File "%s" already exists', $filename), 231125133623);
        }

        if ($output !== null) {
            $output->info('Writing the following contents to ' . $filename . "\n\n" . $contents);
        }

        $dir = dirname($filename);
        if (!is_dir($dir)) {
            \Safe\mkdir($dir, recursive: true);
        }
        \Safe\file_put_contents($filename, $contents);

        if ($addToGit) {
            exec('git add ' . escapeshellarg($filename));
        }
    }
}
