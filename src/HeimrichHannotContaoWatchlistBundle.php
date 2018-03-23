<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 19.03.18
 * Time: 10:45
 */

namespace HeimrichHannot\WatchlistBundle;


use HeimrichHannot\WatchlistBundle\DependencyInjection\HeimrichHannotContaoWatchlistExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoWatchlistBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new HeimrichHannotContaoWatchlistExtension();
    }
    
}