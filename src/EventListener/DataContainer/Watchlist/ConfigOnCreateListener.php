<?php

namespace HeimrichHannot\WatchlistBundle\EventListener\DataContainer\Watchlist;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use HeimrichHannot\WatchlistBundle\Model\WatchlistModel;
use HeimrichHannot\WatchlistBundle\Watchlist\AuthorType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsCallback(table: 'tl_watchlist', target: 'config.oncreate')]
class ConfigOnCreateListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    )
    {
    }

    public function __invoke(string $table, int $id, array $data, DataContainer $dc): void
    {
        if ($table !== WatchlistModel::getTable()) {
            return;
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (!($user instanceof BackendUser)) {
            return;
        }

        $model = WatchlistModel::findByPk($id);
        if (null === $model) {
            return;
        }

        $model->authorType = AuthorType::USER->value;
        $model->author = $user->id;
        $model->save();
    }
}