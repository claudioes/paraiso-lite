<?php

namespace App\Mails;

use App\Mailer\Mailable;
use App\Models\ActionVerification;

class ActionVerificationCreatedMail extends Mailable
{
    protected $verification;

    public function __construct(ActionVerification $verification)
    {
        $this->verification = $verification;
    }

    public function build()
    {
        return $this->subject("Verificación de acción planificada")
            ->view('mails/action_verification_created.twig');
    }
}
