<?php

namespace App\Mails;

use App\Mailer\Mailable;
use App\Models\RiskTask;

class RiskTaskDeletedMail extends Mailable
{
    protected $task;

    public function __construct(RiskTask $task)
    {
        $this->task = $task;
    }

    public function build()
    {
        return $this->subject("Acción de R/O eliminada")
            ->view('mails/risk_task_deleted.twig');
    }
}
