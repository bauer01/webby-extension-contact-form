<?php

namespace Webby\Extensions\ContactForm;

use Nette\DI\CompilerExtension;

class Extension extends CompilerExtension
{

    private $defaults = [];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('factory'))
            ->setClass(Factory::class);

    }

}