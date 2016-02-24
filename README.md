# fixtures-boost-bundle

Provides a console command that allow to analyze all project's fixtures changes and load fixtures data via quick mysql dump, instead of reload data from slow fixtures

Installation
============

  1. Add it to your composer.json:

    ```json
    {
      "require-dev": {
          "gamma/fixtures-boost-bundle": "dev-master"
      }
    }
    ```

    or:

    ```sh
      composer require gamma/fixtures-boost-bundle
      composer update gamma/fixtures-boost-bundle
    ```

  2. Add this bundle to your application kernel in test enviroment:

    ```php
     // app/AppKernel.php
     public function registerBundles()
     {
         ...
         if (in_array($this->getEnvironment(), array('dev', 'test'))) {
         ...
            $bundles[] = new Gamma\FixturesBoostBundle\FixturesBoostBundle();
         }
     }
    ```