<?php

declare(strict_types=1);

/*
 * This file is part of the PHP Domain Parser Symfony-bridge package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\PdbSfBridge\Console;

use Rollerworks\Component\PdbSfBridge\PdpManager;
use Rollerworks\Component\PdbSfBridge\StaticPdpManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'rollerworks-pdb:update', description: 'Update the Public-suffix and TDL lists')]
final class UpdateListsCommand extends Command
{
    public function __construct(private PdpManager $pdpManager)
    {
        parent::__construct('rollerworks-pdb:update');
    }

    protected function configure(): void
    {
        $this
            ->addArgument('suffix-list', InputArgument::REQUIRED, 'public suffix list file path')
            ->addArgument('tld-list', InputArgument::REQUIRED, 'public suffix list file path')
            ->setHelp(<<<'EOF'
                The <info>%command.name%</info> updates the PDB cached lists.

                First download the lists from https://publicsuffix.org/list/public_suffix_list.dat
                and https://data.iana.org/TLD/tlds-alpha-by-domain.txt respectively.

                  <info>php %command.full_name% public_suffix_list.dat tlds-alpha-by-domain.txt</info>

                EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        if (! $this->pdpManager instanceof StaticPdpManager) {
            $style->error('Can only use this command wth the StaticPdpManager.');

            return 1;
        }

        $this->pdpManager->populateCaches(
            self::getFilePath($input->getArgument('suffix-list')),
            self::getFilePath($input->getArgument('tld-list'))
        );

        $style->success('Lists were updated successfully.');

        return 0;
    }

    private static function getFilePath(string $filename): string
    {
        if (file_exists($filename)) {
            return $filename;
        }

        if (! self::isAbsolute($filename)) {
            $filename = getcwd() . \DIRECTORY_SEPARATOR . $filename;

            if (! file_exists($filename)) {
                return throw new \InvalidArgumentException(\sprintf('Unable to locate file "%s"', $filename));
            }

            return $filename;
        }

        if (! file_exists($filename)) {
            return throw new \InvalidArgumentException(\sprintf('File does not exist at "%s"', $filename));
        }

        return $filename;
    }

    /** @see https://github.com/symfony/symfony/blob/6.4/src/Symfony/Component/Filesystem/Path.php */
    public static function isAbsolute(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        // Strip scheme
        $schemeSeparatorPosition = mb_strpos($path, '://');

        if ($schemeSeparatorPosition !== false) {
            $path = mb_substr($path, $schemeSeparatorPosition + 3);
        }

        $firstCharacter = $path[0];

        // UNIX root "/" or "\" (Windows style)
        if ($firstCharacter === '/' || $firstCharacter === '\\') {
            return true;
        }

        // Windows root
        if (mb_strlen($path) > 1 && ctype_alpha($firstCharacter) && $path[1] === ':') {
            // Special case: "C:"
            if (mb_strlen($path) === 2) {
                return true;
            }

            // Normal case: "C:/ or "C:\"
            if ($path[2] === '/' || $path[2] === '\\') {
                return true;
            }
        }

        return false;
    }
}
