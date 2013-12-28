<?php

namespace WeavingTheWeb\Bundle\ApiBundle\Search;

use FOS\ElasticaBundle\Doctrine\ORM\Provider;

class UserStatusProvider extends Provider
{
    /**
     * @see FOS\ElasticaBundle\Provider\ProviderInterface::populate()
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $queryBuilder = $this->createQueryBuilder();
        $nbObjects = $this->countObjects($queryBuilder);
        $offset = isset($options['offset']) ? intval($options['offset']) : 0;
        $sleep = isset($options['sleep']) ? intval($options['sleep']) : 0;
        $batchSize = isset($options['batch-size']) ? intval($options['batch-size']) : $this->options['batch_size'];

        for (; $offset < $nbObjects; $offset += $batchSize) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }
            $objects = $this->fetchSlice($queryBuilder, $batchSize, $offset);

            $this->objectPersister->insertMany($objects);
//            foreach ($objects as $object) {
//                $object->setIndexed(true);
//            }
//
//            $manager = $this->managerRegistry->getManager();
//            $manager->persist($object);
//            $manager->flush();

            if ($this->options['clear_object_manager']) {
                $this->managerRegistry->getManagerForClass($this->objectClass)->clear();
            }

            usleep($sleep);

            if ($loggerClosure) {
                $stepNbObjects = count($objects);
                $stepCount = $stepNbObjects + $offset;
                $percentComplete = 100 * $stepCount / $nbObjects;
                $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
                $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s', $percentComplete, $stepCount, $nbObjects, $objectsPerSecond));
            }
        }
    }
}