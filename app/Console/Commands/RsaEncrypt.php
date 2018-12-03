<?php

namespace App\Console\Commands;

use App\Services\CryptService;
use Illuminate\Console\Command;

class RsaEncrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rsa:encrypt {data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '运营商加密结果';

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
        $arguments = $this->argument();
        $data = $arguments["data"];
        //
        $public_key = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC0OKPZNyxd6avlElj+2sN5b0I1
wA0e+MEE0l+c9XlL0mQwufhk2/s634RjhbHMXQnz9SKSPeW1SKytGAeb9eK+m5ya
+a1lwSByT7T9U3T+t7dP6PeAn3sLCQqtFevRZGuuM0pEFof6FvV+qi3wBDKQbiOh
S1kJNGUWnqOcQSy/GQIDAQAB
-----END PUBLIC KEY-----";
        $encrypt = CryptService::encrypt('rsa', $public_key, $data);
        print_r($encrypt . "\n");
    }
}
