<?php

namespace EvoMark\EvoLaravelBrevo\Transports;

use Brevo\Client\Configuration;
use Symfony\Component\Mime\Email;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailCc;
use Brevo\Client\Model\SendSmtpEmailTo;
use Brevo\Client\Model\SendSmtpEmailBcc;
use Symfony\Component\Mailer\SentMessage;
use Brevo\Client\Model\SendSmtpEmailSender;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmailReplyTo;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class BrevoTransport extends AbstractTransport
{
    protected Configuration $config;
    protected TransactionalEmailsApi $client;

    /**
     * Create a new Brevo transport instance.
     */
    public function __construct()
    {
        $this->config = \Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', config('brevo.api-key'));
        $this->client = new \Brevo\Client\Api\TransactionalEmailsApi(
            new \GuzzleHttp\Client(),
            $this->config
        );
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $message = new \Brevo\Client\Model\SendSmtpEmail();
        $message->setTo($this->getTo($email));
        $message->setSender($this->getSender($email));
        $message->setHtmlContent($email->getHtmlBody());
        $message->setTextContent($email->getTextBody());
        $message->setSubject($email->getSubject());

        $this->addOptional($message, $email);

        $this->client->sendTransacEmail($message);
    }

    private function getTo(Email $email): array
    {
        $symfonyTo = collect($email->getTo());

        return $symfonyTo->map(function ($person) {
            $personName = $person->getName();
            return new SendSmtpEmailTo([
                'email' => $person->getAddress(),
                'name' => !empty($personName) ? $personName : $this->getNameFromEmail($person->getAddress())
            ]);
        })->toArray();
    }

    private function getNameFromEmail(string $email): string
    {
        return str($email)->before('@')->replace(".", " ")->title()->value;
    }

    private function getSender(Email $email): SendSmtpEmailSender
    {
        $symfonySender = collect($email->getFrom())->first();

        return new SendSmtpEmailSender([
            'name' => $symfonySender->getName(),
            'email' => $symfonySender->getAddress()
        ]);
    }

    private function addOptional(SendSmtpEmail $message, Email $mail)
    {
        $replyTo = collect($mail->getReplyTo())->first();
        if (!empty($replyTo)) {
            $replyToName = $replyTo->getName();
            $replyToBrevo = new SendSmtpEmailReplyTo([
                'email' => $replyTo->getAddress(),
                'name' => !empty($replyToName) ? $replyToName : $this->getNameFromEmail($replyTo->getAddress())
            ]);
            $message->setReplyTo($replyToBrevo);
        }

        $cc = collect($mail->getCc());
        if (!empty($cc) && $cc->count() > 0) {
            $brevoCcs = $cc->map(function ($item) {
                $ccName = $item->getName();

                return new SendSmtpEmailCc([
                    'email' => $item->getAddress(),
                    'name' => !empty($ccName) ? $ccName : $this->getNameFromEmail($item->getAddress())
                ]);
            })->toArray();
            $message->setCc($brevoCcs);
        }

        $bcc = collect($mail->getBcc());
        if (!empty($bcc) && $bcc->count() > 0) {
            $brevoBccs = $bcc->map(function ($item) {
                $bccName = $item->getName();

                return new SendSmtpEmailBcc([
                    'email' => $item->getAddress(),
                    'name' => !empty($bccName) ? $bccName : $this->getNameFromEmail($item->getAddress())
                ]);
            })->toArray();
            $message->setBcc($brevoBccs);
        }
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'brevo';
    }
}
