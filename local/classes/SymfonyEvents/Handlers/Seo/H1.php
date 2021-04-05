<?php

namespace Local\SymfonyEvents\Handlers\Seo;

use Local\SymfonyEvents\Events\ResultModifierSectionsEvent;

/**
 * Class H1
 * @package Local\SymfonyEvents\Handlers\Seo
 */
class H1
{
    /**
     * Калькуляция H1.
     *
     * @param ResultModifierSectionsEvent $event Объект события.
     *
     * @return void
     */
    public function action(ResultModifierSectionsEvent $event): void
    {
        $h1 = $event->arParams('H1_PROPERTY') ?: $event->arResult('NAME');

        $event->setResult($event->arResult(), 'H1', $h1);
    }

}
