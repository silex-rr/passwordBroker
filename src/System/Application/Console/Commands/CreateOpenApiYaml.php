<?php

namespace System\Application\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use OpenApi\Generator;
use Symfony\Component\Console\Command\Command as CommandAlias;
use System\Application\Services\BackupService;

class CreateOpenApiYaml extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:createOpenApiYaml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $openApi = Generator::scan([base_path("/src")]);
        if (!$openApi) {
            $this->error("OpenApi  cannot be created");
            return CommandAlias::FAILURE;
        }
        $yml_file = base_path("openApi.yaml");
        file_put_contents($yml_file, $openApi->toYaml());
        $this->info('new /openApi.yaml has been created');
        return CommandAlias::SUCCESS;
    }
}
