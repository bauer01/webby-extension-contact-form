<?php

namespace Webby\Extensions\ContactForm;

use Nette\DI\CompilerExtension;

class Extension extends CompilerExtension
{

    private $defaults = [
        "forms" => []
    ];

    public function loadConfiguration()
    {
        $config = $this->getConfig($this->defaults);
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('factory'))
            ->setClass(
                Factory::class,
                [
                    $config["forms"]
                ]
            );

    }

}