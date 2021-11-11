<?php namespace System\Console;

use Illuminate\Console\Command;
use System\Classes\UpdateManager;
use Exception;

/**
 * OctoberUpdate performs a system update.
 *
 * This updates October CMS and all plugins, database and libraries.
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class OctoberUpdate extends Command
{
    /**
     * @var string name of console command
     */
    protected $name = 'october:update';

    /**
     * @var string description of the console command
     */
    protected $description = 'Updates October CMS and all plugins, database and files.';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        $this->output->writeln('<info>Updating October CMS...</info>');

        $this->comment("Executing: composer update");
        $this->output->newLine();

        // Composer update
        $errCode = null;
        passthru('composer update', $errCode);

        if ($errCode !== 0) {
            $this->output->error('Update failed. Check output above');
            exit(1);
        }

        $this->comment("Executing: php artisan october:migrate");
        $this->output->newLine();

        // Migrate database
        $errCode = null;
        passthru('php artisan october:migrate', $errCode);

        if ($errCode !== 0) {
            $this->output->error('Migration failed. Check output above');
            exit(1);
        }

        try {
            $this->output->success(sprintf('System Updated to v%s', UpdateManager::instance()->getCurrentVersion()));
        }
        catch (Exception $ex) {
            // ...
        }
    }
}
