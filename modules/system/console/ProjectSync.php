<?php namespace System\Console;

use System;
use System\Classes\UpdateManager;
use October\Rain\Process\Composer as ComposerProcess;
use Illuminate\Console\Command;
use Exception;

/**
 * ProjectSync installs all plugins and themes belonging to a project
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class ProjectSync extends Command
{
     /**
     * @var string name of console command
     */
    protected $name = 'project:sync';

    /**
     * @var string description of the console command
     */
    protected $description = 'Install plugins and themes belonging to a project.';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        $this->output->writeln('<info>Synchronizing Project...</info>');

        try {
            // Install project packages
            $this->installDefinedPlugins();

            // Check dependencies
            passthru('php artisan plugin:check --no-migrate');

            // Lock themes
            if (System::hasModule('Cms')) {
                passthru('php artisan theme:check');
            }

            $this->output->success("Project synchronized");

            // Run migrations
            $this->comment('Please migrate the database with the following command');
            $this->output->newLine();
            $this->line("* php artisan october:migrate");
            $this->output->newLine();
        }
        catch (Exception $e) {
            $this->output->error($e->getMessage());
        }
    }

    /**
     * installDefinedPlugins
     */
    protected function installDefinedPlugins()
    {
        $installPackages = UpdateManager::instance()->syncProjectPackages();

        // Nothing to do
        if (count($installPackages) === 0) {
            $this->info('All packages already installed');
            return;
        }

        // Composer install differences
        $requirePackages = implode(' ', $installPackages);
        $this->comment("Executing: composer require {$requirePackages}");
        $this->output->newLine();

        $composer = new ComposerProcess;
        $composer->setCallback(function($message) { echo $message; });
        $composer->require($requirePackages);

        if ($composer->lastExitCode() !== 0) {
            $this->output->error('Sync failed. Check output above');
            exit(1);
        }
    }
}
