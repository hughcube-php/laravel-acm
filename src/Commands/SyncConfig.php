<?php

namespace HughCube\Laravel\ACM\Commands;

use HughCube\Laravel\ACM\ACM;
use HughCube\Laravel\ACM\Client\Client;
use Illuminate\Console\Command;

class SyncConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acm:sync-config
        {dataId : Configured ID }
        {file   : Configure saved file path }
        {--client=default : Name of the ACM client, default "default" }
        {--repeat=-1 : Number of repeated checks, -1 means unlimited, default "-1" }
        {--interval=1000 : Every check interval MS, default 1000 ms }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save the configuration of pull ACM to a file';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     *
     */
    public function handle()
    {
        /** @var int $repeat */
        $repeat = $this->option('repeat');
        $interval = $this->option('interval');
        $dataId = $this->argument('dataId');
        $file = $this->argument('file');

        $runCount = 0;
        do {
            $runCount > 0 and usleep($interval * 1000);
            $this->syncConfig($this->getClient()->withConfig([]), $dataId, $file);
            $runCount++;
        } while (-1 == $repeat || $runCount < $repeat);
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        return ACM::client($this->option('client'));
    }

    /**
     * @return bool
     * @throws \Exception
     *
     */
    protected function syncConfig(Client $client, $dataId, $file)
    {
        $localContents = (is_file($file) ? file_get_contents($file) : null);
        $localHash = $this->hashContents($localContents);

        $remoteContents = $client->read($dataId);
        $remoteHash = $this->hashContents($remoteContents);

        if ($localHash === $remoteHash) {
            $this->info(sprintf('%s <info>文件无需同步, hash: %s</info>', date('Y-m-d H:i:s'), $remoteHash));
        } else {
            file_put_contents($file, $remoteContents, LOCK_EX);
            $this->info(sprintf('%s <info>文件同步成功, hash: %s</info>', date('Y-m-d H:i:s'), $remoteHash));
        }

        return true;
    }

    /**
     * @param string|null $contents
     *
     * @return string|null
     */
    protected function hashContents($contents)
    {
        if (null === $contents) {
            return null;
        }

        return md5($contents) . '-' . crc32($contents);
    }
}
