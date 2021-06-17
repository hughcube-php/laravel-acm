<?php

namespace HughCube\Laravel\ACM\Commands;

use HughCube\Laravel\ACM\ACM;
use HughCube\Laravel\ACM\Client\Client;

class SyncAppConfig extends SyncConfig
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acm:sync-app-config
        {dataId : Name of the ACM dataId }
        {file   : Configure saved file path }
        {--client=NULL : Name of the ACM client, default "default" }
        {--AccessKeyID=NULL : ACM AccessKeyID, default "NULL" }
        {--AccessKeySecret=NULL : ACM AccessKeySecret, default "NULL" }
        {--Namespace=NULL : ACM Namespace, default "NULL" }
        {--Group=NULL : ACM Group, default "NULL" }
        {--Proxy=NULL : ACM Proxy, default "NULL" }
        {--repeat=-1 : Number of repeated checks, -1 means unlimited, default "-1" }
        {--interval=1000 : Every check interval MS, default 1000 ms }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save the APP configuration of pull ACM to a file';

    /**
     * @return Client
     */
    protected function getClient()
    {
        if (null != $this->getOptionValue('AccessKeyID')) {
            return new Client([
                'AccessKeyID' => $this->getOptionValue('AccessKeyID'),
                'AccessKeySecret' => $this->getOptionValue('AccessKeySecret'),
                'Namespace' => $this->getOptionValue('Namespace'),
                'Group' => $this->getOptionValue('Group'),
                'Proxy' => $this->getOptionValue('Proxy'),
            ]);
        }

        return ACM::client($this->getOptionValue('client'));
    }

    /**
     * @param string $name
     * @return array|bool|string|null
     */
    protected function getOptionValue($name)
    {
        $value = $this->option($name);
        return 'NULL' === $value ? null : $value;
    }
}
