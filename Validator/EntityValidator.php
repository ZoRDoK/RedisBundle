<?php

namespace Filth\RedisBundle\Validator;

/**
 * This checks if all redis keys and aliases are unique
 *
 */
use Filth\RedisBundle\Annotation\RedisAnnotation;

class EntityValidator
{
    private $entities = null;

    public function __construct($entities)
    {
        $this->entities = $entities;
    }

    public function validate($container)
    {
        $keyArray   = array();
        $aliasArray = array();

        foreach ($this->entities as $entity) {

            $reflectionClass = new \ReflectionClass($entity['class']);
            $reader = new \Doctrine\Common\Annotations\AnnotationReader();
            $annotation = $reader->getClassAnnotation($reflectionClass, new RedisAnnotation(array()));

            $redisKey = $annotation->getRedisKey();

            // check if a key or alias was redefined. if so throw an exception
            if($redisKey != null)
            {
                if(!isset($keyArray[$redisKey]))
                {
                    $keyArray[$redisKey] = $entity['class'];

                    if(isset($aliasArray[$entity['alias']])) throw new \Exception('Alias '.$entity['alias'].' was redefined. Please check!');
                    $aliasArray[$entity['alias']] = $entity['class'];
                }
                else
                {
                    throw new \Exception('Redis Key: '.$redisKey.' was redefined. Last seen in class: '.$entity['class'].'. Please fix!');
                }
            }
        }

        $container->setParameter('filth_redisbundle_entity_keys', $keyArray);     // hier stehen key -> class mappings drin
        $container->setParameter('filth_redisbundle_entity_aliases', $aliasArray);// hier stehen alias -> class mappings drin
    }
}
