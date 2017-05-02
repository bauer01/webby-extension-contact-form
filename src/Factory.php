<?php

namespace Webby\Extensions\ContactForm;


use Latte\Engine;
use Nette\Forms\Form;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Tracy\Debugger;
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
                $id,
                $form->getValues()["email"],
                $options["to"],
                $options["subject"],
                empty($form->getValues()["message"]) ? null : $form->getValues()["message"],
                empty($form->getValues()["phone"]) ? null : $form->getValues()["phone"]
            );

            $this->message = $options["success"];

            if ($presenter->isAjax()) {
                $presenter->invalidate([$particle]);
            } else {
                $presenter->redirect($link);
            }
        }

        return $form;
    }

    private function send($id, $from, $to, $subject, $message, $phone)
    {
        Debugger::log("Contact form - " . json_encode([$id, $from, $to, $subject, $message, $phone]), "mail");

        $latte = new Engine();
        $mail = new Message();
        $mail->setFrom($from)
            ->setSubject($subject)
            ->addTo($to)
            ->setHtmlBody(
                $latte->renderToString(
                    __DIR__ . '/templates/default.latte',
                    [
                        "message" => $message,
                        "phone" => $phone
                    ]
                )
            );
        $this->mailer->send($mail);
    }

}