<?php

namespace modules;
use Craft;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;
use yii\base\Event;

class Module extends \yii\base\Module
{
    public function init()
    {
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                if (Craft::$app->config->general->devMode) return;
                $pluginStoreIndex = array_search(
                    'plugin-store',
                    array_column($event->navItems, 'url')
                );
                if ($pluginStoreIndex === false) return;
                unset($event->navItems[$pluginStoreIndex]);
            }
        );

        parent::init();
    }
}