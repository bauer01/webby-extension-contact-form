<?php

namespace Webby\Extensions\ContactForm;

use Nette\Forms\Form;
use Webby\Presenter\DefaultPresenter;

class Factory
{

    public function create(DefaultPresenter $presenter, $link, array $options)
    {
        $form = new Form();
        foreach ($options["inputs"] as $input) {
            switch ($input['type']) {
                case "text":
                    $form->addText($input["name"], $input["label"]);
                    break;
                case "textarea":
                    $form->addTextArea($input["name"], $input["label"]);
                    break;
            }
        }
        $form->addSubmit("send", $options["submit_label"]);

        if ($form->isSuccess()) {
            if ($presenter->isAjax()) {
                $presenter->sendAjax($link, []);
            } else {
                $presenter->sendRedirect($link);
            }
        }

        return $form;
    }

}