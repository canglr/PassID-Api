<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail;
use App\Helpers\Helpers as Helpers;

class Mailler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:listesi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mail Listesi';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {		
		$mailler = Mail::where('durum', '=', false)->get();
			foreach($mailler as $mail)
			{
			    Helpers::mailgonder($mail->mailadresi,$mail->baslik,$mail->mesaj,$mail->sablon);
				$mailupdate = Mail::find($mail->id);
				$mailupdate->durum = true;
				$mailupdate->save();
			}
        
    }
}
