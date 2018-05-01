<?php declare(strict_types=1);

namespace TomasVotruba\Website\Statie\Generator;

use Symplify\Statie\Generator\Contract\ObjectSorterInterface;
use Symplify\Statie\Generator\FileNameObjectSorter;
use Symplify\Statie\Renderable\File\AbstractFile;
use TomasVotruba\Website\Statie\Exception\DuplicatedIdException;
use TomasVotruba\Website\Statie\Exception\MissingIdException;

final class PostFileSorter implements ObjectSorterInterface
{
    /**
     * @var FileNameObjectSorter
     */
    private $fileNameObjectSorter;

    public function __construct()
    {
        $this->fileNameObjectSorter = new FileNameObjectSorter();
    }

    /**
     * @param AbstractFile[] $files
     * @return AbstractFile[]
     */
    public function sort(array $files): array
    {
        $files = $this->fileNameObjectSorter->sort($files);

        return $this->useIdsAsArrayKeys($files);
    }

    /**
     * @param AbstractFile[] $abstractFiles
     * @return AbstractFile[]
     */
    private function useIdsAsArrayKeys(array $abstractFiles): array
    {
        $arrayWithIdAsKey = [];

        foreach ($abstractFiles as $abstractFile) {
            $this->ensureIdIsValid($abstractFile, $arrayWithIdAsKey);

            $arrayWithIdAsKey[$abstractFile->getId()] = $abstractFile;
        }

        return $arrayWithIdAsKey;
    }

    /**
     * @param AbstractFile[] $arrayWithIdAsKey
     */
    private function ensureIdIsValid(AbstractFile $abstractFile, array $arrayWithIdAsKey): void
    {
        $this->ensureIdIsSet($abstractFile);
        $this->ensureIdIsUnique($arrayWithIdAsKey, $abstractFile->getId());
    }

    private function ensureIdIsSet(AbstractFile $postFile): void
    {
        if ($postFile->getId()) {
            return;
        }

        throw new MissingIdException(sprintf(
            'File "%s" is missing "id:" in its configuration. Complete it.',
            $postFile->getFilePath()
        ));
    }

    /**
     * @param AbstractFile[] $arrayWithIdAsKey
     */
    private function ensureIdIsUnique(array $arrayWithIdAsKey, int $id): void
    {
        if (! isset($arrayWithIdAsKey[$id])) {
            return;
        }

        throw new DuplicatedIdException(sprintf('Id "%d" was already set. Change it', $id));
    }
}