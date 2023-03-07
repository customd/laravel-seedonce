<?php

namespace CustomD\SeedOnce\Commands;

use CustomD\SeedOnce\Repositories\SeederRepositoryInterface as Repository;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseCommand extends Command
{
    /**
     * The connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * Seeder repository
     *
     * @var \CustomD\SeedOnce\Repositories\SeederRepositoryInterface
     */
    protected $repository;

    /**
     * Create a new database seed command instance.
     *
     * @return void
     */
    public function __construct(Resolver $resolver,
                                Filesystem $files,
                                Container $container,
                                Repository $repository)
    {
        parent::__construct();

        $this->resolver = $resolver;
        $this->files = $files;
        $this->container = $container;
        $this->repository = $repository;
    }

    /**
     * Get the seeders to mark as seeded.
     * NOTE: Main Database Seeder is always excluded.
     *
     * @param  string  $classOption Which class to get. "all" for all classes.
     * @return Collection<(int|string), string>
     */
    protected function getSeeders($classOption = 'all')
    {
        // Read all files from the database/seeds directory
        $seedersPath = $this->getSeederFolder();

        return Collection::make([$seedersPath])
            ->flatMap(function ($path) {
                return Str::endsWith($path, '.php') ? [$path] : $this->files->glob($path.'*.php');
            })
            ->map(function ($path) {
                return $this->getSeederName($path);
            })
            ->filter(function ($class) use ($classOption) {
                // Filter out classes based on option passed
                return ($classOption === 'all' || $classOption === $class)
                    // We want to skip DatabaseSeeder as we never mark it as seeded
                    && class_basename($class) !== class_basename(config('seedonce.database_seeder'));
            });
    }

    /**
     * Get the name of the seeder class.
     *
     * @param  string  $path
     * @return string
     */
    protected function getSeederName($path)
    {
        return $this->getSeederNamespace().str_replace('.php', '', class_basename($path));
    }

    /**
     * Get the name of the database connection to use.
     *
     * @return string
     */
    protected function getDatabase()
    {
        $database = $this->input->getOption('database');

        return $database ?: $this->laravel['config']['database.default'];
    }

    /**
     * @return string
     */
    protected function getSeederFolder()
    {
        return $this->laravel->databasePath().DIRECTORY_SEPARATOR.config('seedonce.folder_seeder').DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    protected function getSeederNamespace()
    {
        $composerJsonPath = base_path('composer.json');
        $composerConfig = json_decode(file_get_contents($composerJsonPath), true);
        $seederConfigPath = config('seedonce.folder_seeder');

        if ((float) app()->version() >= 8) {
            $items = array_filter($composerConfig['autoload']['psr-4'], function ($item) use ($seederConfigPath) {
                return Str::contains($item, $seederConfigPath);
            });

            return array_keys($items)[0] ?? '';
        }

        return '';
    }
}
