<?php

$rootDir = dirname(__FILE__) . '/../../../../..';
require_once dirname(__FILE__) . '/../../bootstrap/unit.php';

$dispatcher = new sfEventDispatcher();
require_once $rootDir.'/config/ProjectConfiguration.class.php';
$configuration = new ProjectConfiguration($rootDir, $dispatcher);

class sfPropelDummyTask extends sfPropelBaseTask
{

    public function checkProjectExists()
    {
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connections = $this->getConnections($databaseManager);

        $t = new lime_test(count($connections));
        $t->diag('->getConnections()');

        foreach ($connections as $name => $connection) {
            $databaseInstance = $databaseManager->getDatabase($name);
            $t->ok(
                $databaseInstance instanceof sfPropelDatabase,
                sprintf(
                    '->getConnections() should return only sfPropelDatabase instance. "%s" returned',
                    get_class($databaseInstance)
                )
            );
        }
    }

}

$task = new sfPropelDummyTask($dispatcher, new sfFormatter());
$task->run();
