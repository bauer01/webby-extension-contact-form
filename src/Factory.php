<?php

namespace Webby\Extensions\ContactForm;


use Latte\Engine;
use Nette\Forms\Form;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Webby\Presenter\DefaultPresenter;

class Factory
{

    private $mailer;
    private $forms;
    private $message;

    public function __construct(array $forms, IMailer $mailer)
    {
        $this->forms = $forms;
        $this->mailer = $mailer;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function create(DefaultPresenter $presenter, $link, $id, $particle)
    {
        $form = new Form("contact-" . $id);

        $options = $this->forms[$id];
        $form->addText("email", $options["email"]["label"])
            ->addRule(Form::EMAIL, $options["email"]["validation"])
            ->setRequired();
        $form->addText("phone", $options["phone"]["label"])
            ->setRequired();
        if (!empty($options["message"])) {
            $form->addTextArea("message", $options["message"]["label"])
                ->setRequired();
        }
        $form->addProtection($options["csrf"]);
        $form->addSubmit("send", $options["submit"]);

        if ($form->isSuccess()) {

            $this->send(
                $form->getValues()["email"],
                $options["to"],
                $options["subject"],
                empty($form->getValues()["message"]) ? null : $form->getValues()["message"]
            );

            $this->message = $options["success"];

            if ($presenter->isAjax()) {
                $presenter->getParticles()->invalidate([$particle]);
            } else {
                $presenter->redirect($link);
            }
        }

        return $form;
    }

    private function send($from, $to, $subject, $message)
    {
        $latte = new Engine();
        $mail = new Message();
        $mail->setFrom($from)
            ->setSubject($subject)
            ->addTo($to)
            ->setHtmlBody(
                $latte->renderToString(
                    __DIR__ . '/templates/default.latte',
                    [
                        "message" => $message
                    ]
                )
            );
        $this->mailer->send($mail);
    }

}