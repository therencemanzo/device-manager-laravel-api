<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\SendEmailNotificationForDeactivatedDevice;
use Mail;

class SendEmailToUserWhenDeviceDeactivated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $details)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $email = new SendEmailNotificationForDeactivatedDevice();

        Mail::to($this->details['email'])->send($email);
       
    }
}
