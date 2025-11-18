<?php

namespace HeimrichHannot\WatchlistBundle\Item;

use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\Image;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Model\WatchlistConfigModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use Symfony\Component\Uid\Uuid;

class WatchlistItem
{
    private readonly WatchlistItemType $type;
    private ?FilesystemItem $file;
    private ?WatchlistConfigModel $config;
    private ?Figure $figure;

    public function __construct(
        private readonly WatchlistItemModel $model,
        private readonly VirtualFilesystemInterface $filesystem,
        private readonly Studio $studio,
    )
    {
        $this->type = WatchlistItemType::from($this->model->type ?? WatchlistItemType::FILE->value);
    }

    public function getType(): WatchlistItemType
    {
        return $this->type;
    }

    public function fileExist(): bool
    {
        $this->resolveFile();
        return $this->file !== null;
    }

    public function getPath(): ?string
    {
        $this->resolveFile();
        return $this->file?->getPath();
    }

    public function getModel(): ?WatchlistItemModel
    {
        return $this->model;
    }

    public function applyToTemplateData(array $data): array
    {
        return match ($this->type) {
            WatchlistItemType::FILE => $this->fileTemplateData($data),
            WatchlistItemType::ENTITY => $this->entityTemplateData($data),
        };
    }

    public function getImage(): ?Figure
    {
        if (isset($this->figure)) {
            return $this->figure;
        }

        $this->resolveFile();
        if (!$this->fileExist()) {
            return null;
        }

        $figureBuilder = $this->studio->createFigureBuilder()
            ->fromUuid($this->file->getUuid())
            ->enableLightbox();;

        if ($this->getConfig()?->imgSize) {
            $figureBuilder->setSize($this->getConfig()->imgSize);
        }

        $this->figure = $figureBuilder->buildIfResourceExists();
        return $this->figure;
    }

    public function getFile(): ?FilesystemItem
    {
        $this->resolveFile();
        return $this->file;
    }

    private function resolveFile(): void
    {
        if (isset($this->file)) {
            return;
        }

        $uuid = match ($this->type) {
            WatchlistItemType::FILE => $this->model->file,
            WatchlistItemType::ENTITY => $this->model->entityFile,
        };
        $uuid = Uuid::fromString($uuid);
        $this->file = $this->filesystem->get($uuid);
    }

    private function getConfig(): ?WatchlistConfigModel
    {
        if (isset($this->config)) {
            return $this->config;
        }

        $watchlist = WatchlistModel::findByPk($this->model->pid);
        if (!$watchlist) {
            $this->config = null;
            return $this->config;
        }
        $this->config = WatchlistConfigModel::findByPk($watchlist->config);
        return $this->config;
    }

    private function fileTemplateData(array $data): array
    {
        if (!$this->fileExist()) {
            $data['existing'] = false;
            $data['fileItem'] = null;
            $data['file'] = '';
            return $data;
        }

        $data['existing'] = true;
        $data['fileItem'] = $this->file;
        $data['file'] = (string)$this->file->getUuid();

        // create the url with file-GET-parameter so that also nonpublic files can be accessed safely
//        $url = $this->insertTagParser->replace('{{download_link::' . $file->path . '}}');
//        $query = parse_url((string)$url, \PHP_URL_QUERY);
//        $url = $this->utils->url()->addQueryStringParameterToUrl($query, $currentUrl);
//
//        $cleanedItem['downloadUrl'] = $this->utils->url()->removeQueryStringParameterFromUrl(['wl_root_page', 'wl_url'], $url);

        $data['downloadUrl'] = $this->filesystem->generatePublicUri($this->file->getUuid());

        if (empty($data['title'])) {
            $data['title'] = $this->file->getName();
        }

        $data['filesize'] = System::getReadableSize($this->file->getFileSize());
        $data['icon'] = Image::getPath($GLOBALS['TL_MIME'][$this->file->getExtension(true)][1]);
        $data['hash'] = md5(implode('_', array_filter([$this->getType()->value, $this->model->pid, $this->model->file])));
        $data['figure'] = $this->getImage();

        return $data;
    }

    private function entityTemplateData(array $data): array
    {
        $data['entityTable'] = $this->model->entityTable;
        $data['entity'] = $this->model->entity;
        $data['entityUrl'] = $this->model->entityUrl;


        $data['entityFile'] = (string)$this->getFile()?->getUuid() ?: '';
        $data['existing'] = $this->fileExist();
        $data['hash'] = md5(implode('_', [$this->getType()->value, $this->model->pid, $this->model->entityTable, $this->model->entity]));

//        $cleanedItem['postData'] = htmlspecialchars(json_encode($cleanedItem), \ENT_QUOTES, 'UTF-8');

        $data['figure'] = $this->getImage();

        return $data;
    }
}